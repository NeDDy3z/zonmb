<?php

namespace Controllers;

use Exception;
use Helpers\PrivilegeRedirect;
use Logic\User;
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
     * Constructor
     *
     * Initializes the controller based on the provided action.
     * Handles routing and checks for user privileges for certain actions.
     *
     * @param string|null $action The action to be performed (e.g., 'get', 'add', 'edit', 'delete', etc.)
     * @throws Exception
     */
    public function __construct(?string $action = null)
    {
        $this->privilegeRedirect = new PrivilegeRedirect();

        switch ($action) {
            case 'add':
                break;
            case 'delete':
                $this->privilegeRedirect->redirectHost();

                $this->deleteComment(
                    id: $_GET['id'] ?? null,
                );
                break;
            case 'get':
                $this->getComments(
                    articleId: $_GET['articleId'] ?? null,
                    userId: $_GET['userId'] ?? null,
                    search: $_GET['search'] ?? null,
                    sort: $_GET['sort'] ?? null,
                    sortDirection: $_GET['sortDirection'] ?? null,
                    page: $_GET['page'] ?? 1,
                );
                break;
            default:
                echo json_encode(['error' => 'Invalid action.']);
                break;
        }
    }

    /**
     * Add user comment
     *
     * Call the comment model and insert it into a database
     *
     * @return void
     */
    public function addComment(): void
    {
        $text = $_POST['comment'] ?? null;
        $articleId = (int)$_POST['article'] ?? null;
        $authorId = (int)$_POST['author'] ?? (int)$_SESSION['user_data']['id'];

        if (!$text) {
            echo json_encode(['error' => 'missingComment']);
            exit();
        }

        if (!$articleId) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        if (strlen($text) < 1 or strlen($text) > 255) {
            echo json_encode(['error' => 'commentSize']);
        }

        try {
            CommentModel::insertComment(
                text: $text,
                articleId: $articleId,
                authorId: $authorId,
            );
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit();
    }

    /**
     * Delete a comment from database
     *
     * @param int|null $id
     * @return void
     */
    private function deleteComment(?int $id): void
    {
        if (!isset($id)) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
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

    /**
     * Get all comments based on articleId or userId
     *
     * Build a query and call database
     *
     * @param int|null $articleId
     * @param int|null $userId
     * @return void
     */
    private function getComments(?int $articleId, ?int $userId, ?string $search, ?string $sort, ?string $sortDirection, ?int $page = 1): void
    {
        // Create a query
        try {
            if ($articleId or $userId) {
                $conditions = ($articleId) ? "WHERE article_id = $articleId" : "";
                $conditions .= ($userId) ? (($articleId) ? " AND WHERE user_id = $userId" : "WHERE user_id = $userId") : '';
            } elseif ($search) {
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

            if (!$commentData) {
                throw new Exception('Žádné komentáře nebyly nalezeny');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }

        echo json_encode($commentData);
        exit();
    }
}