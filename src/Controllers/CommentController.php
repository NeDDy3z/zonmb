<?php

namespace Controllers;

use Exception;
use Helpers\DateHelper;
use Helpers\PrivilegeRedirect;
use Logic\User;
use Logic\Validator;
use Models\CommentModel;

/**
 * CommentController
 *
 * The CommentController class is responsible for handling actions related to comments,
 * such as retrieving, adding, and deleting comments
 *
 * It also implements privilege checks where necessary
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class CommentController
{
    /**
     * @var PrivilegeRedirect $privilegeRedirect The privilege redirect instance for redirecting users
     */
    private PrivilegeRedirect $privilegeRedirect;

    /**
     * @var Validator $validator The validator instance for validating user inputs
     */
    private Validator $validator;
    /**
     * Constructor
     *
     * Initializes the controller.
     */
    public function __construct()
    {
        $this->privilegeRedirect = new PrivilegeRedirect();
        $this->validator = new Validator();
    }

    /**
     * Get all comments based on articleId or userId
     *
     * Build a query and call database
     *
     * @return void
     */
    public function getComments(): void
    {
        $articleId = $_GET['article_id'] ?? null;
        $userId = $_GET['user_id'] ?? null;

        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $sortDirection = $_GET['sortDirection'] ?? null;
        $page = $_GET['page'] ?? 1;

        // Create a query
        try {
            if ($articleId or $userId) {
                $conditions = ($articleId) ? "WHERE article_id = $articleId" : "";
                $conditions .= ($userId) ? (($articleId) ? " AND WHERE user_id = $userId" : "WHERE user_id = $userId") : '';
            } elseif ($search) {
                // Convert date format
                $search = DateHelper::ifPrettyConvertToISO($search);
                $conditions = "WHERE comment.id like '%$search%' or comment.text LIKE '%$search%' OR comment.article_id LIKE '%$search%' OR comment.author_id LIKE '%$search%' OR comment.created_at LIKE '%$search%'";
            }

            if (!isset($conditions)) {
                $conditions = "";
            }

            $conditions .= ($sort) ? " ORDER BY $sort" : "";
            $conditions .= ($sortDirection) ? " $sortDirection" : "";
            $conditions .= " LIMIT 10 OFFSET " . ($page - 1) * 10;

            $commentData = CommentModel::selectComments(
                conditions: $conditions,
            );

            echo json_encode($commentData);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit();
    }

    /**
     * Add user comment
     *
     * Call the comment model and insert it into a database
     *
     * @return void
     */
    public function add(): void
    {
        $this->privilegeRedirect->redirectUser();

        $text = $_POST['comment'] ?? null;
        (int)$articleId = $_POST['article'] ?? null;
        (int)$authorId = $_SESSION['user_data']->getId();

        try {
            $this->validator->validateComment(
                articleId: $articleId,
                text: $text,
            );
        } catch (Exception $e) {
            echo json_encode(['error' => explode('-', $e->getMessage())]);
            exit();
        }

        try {
            CommentModel::insertComment(
                text: $text,
                articleId: $articleId,
                authorId: $authorId,
            );

            echo json_encode(['success' => 'commentAdded']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit();
    }

    /**
     * Delete a comment from database
     *
     * @return void
     */
    public function delete(): void
    {
        $this->privilegeRedirect->redirectHost();

        try {
            $id = $_GET['id'] ?? null;
            if (!isset($id)) {
                echo json_encode(['error' => 'missingID']);
                exit();
            }

            // Check if user is author of the comment or admin
            /** @var User $user */
            $user = $_SESSION['user_data'];

            $author = CommentModel::selectComment(conditions: 'WHERE id = ' . $id) ?? null;
            if (!$author) {
                echo json_encode(['error' => 'authorNotFound']);
                exit();
            }

            if ($user->isAdmin() or (int)$author['author_id'] === $user->getId()) {
                CommentModel::removeComment(
                    id: $id,
                );

                echo json_encode(['success' => 'commentDeleted']);
            } else {
                echo json_encode(['error' => 'notAuthorized']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'commentDeleteError']);
        }

        exit();
    }
}