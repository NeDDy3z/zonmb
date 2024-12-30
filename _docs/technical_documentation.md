# Technical Documentation
For more indepth technical documentation, visit the auto-generated [documentation page](phpdoc/index.html)!
**[Website Name Placeholder]** This documentation explains how the web app is structured, codebase and algorithms


## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture Overview](#architecture-overview)
3. [Codebase Structure](#codebase-structure)
4. [Algorithm and Logic Design](#algorithm-and-logic-design)
5. [Scripts](#scripts)
   - [Backend scripts](#backend-scripts)
   - [Frontend scripts](#frontend-scripts)
6. [Features Explanation](#features-explanation)
7. [Configuration](#configuration)
8. [Troubleshooting](#troubleshooting)

---

## Project Overview
- **Project Name**: ZONMB
- **Description**: This project is a simple webapp for ZONMB (Základní organizace neslyšících v Mladé Boleslavi), it allows registration, logging in, article publishing, editing etc.. It uses PHP for backend processing, a MySQL database, and HTML/CSS & JavaScript for the frontend interface"

## Architecture Overview
Describe the overall structure and design patterns used in the project.
- **Core Components**:
    - **Frontend**: HTML / CSS / JavaScript
    - **Backend**: Raw PHP
    - **Database**: MySQL

- **Framework or Libraries Utilized**:
This project _(attempts to)_ implement the MVC architecture where: 
  - Models/ represent the data structure
  - Controllers/ manage application logic
  - Views/ handle user interface rendering

## Codebase Structure
The project scripts and folders structure
**Folder & File Structure**:
```
/"root"
│
├── /_database                              # Database related files
│   └── database_scheme.sql                 # SQL file to create database
│
├── /_docs                                  # Documentation 
├── /bin                                    # Dev tools
├── /log                                    # Error and access log location
│
├── /src                                    # Source of the project - app logic
│   ├── /Controllers                        # Controllers for each page
│   │   ├── AdminController.php
│   │   ├── ArticleController.php
│   │   ├── CommentController.php
│   │   ├── Controller.php
│   │   ├── ErrorController.php
│   │   ├── HomepageController.php
│   │   ├── LoginController.php
│   │   ├── NewsController.php
│   │   ├── RegisterController.php
│   │   ├── TestingController.php
│   │   └── UserController.php
│   │           
│   ├── /Helpers
│   │   ├── DateHelper.php
│   │   ├── ImageHelper.php
│   │   ├── PrivilegeHelper.php
│   │   ├── ReplaceHelper.php
│   │   └── UrlHelper.php
│   │                 
│   │                 
│   ├── /Logic
│   │   ├── Article.php
│   │   ├── DatabaseException.php
│   │   ├── IncorrectInputException.php
│   │   ├── Router.php
│   │   ├── User.php
│   │   └── Validator.php
│   │               
│   ├── /Models                             # Logic for communication with database (User, Article)
│   │   ├── User.php
│   │   ├── Article.php
│   │   └── Comment.php
│   │    
│   └── /Views                              # Templates for individual pages (HTML)
│       ├── /Partials                       # Page partials
│       │   ├── footer.php
│       │   └── header.php
│       │
│       ├── admin.php
│       ├── article.php
│       ├── article-editor.php
│       ├── error.php
│       ├── homepage.php
│       ├── login.php
│       ├── news.php
│       ├── register.php
│       ├── test.php
│       ├── user.php
│       └── user-editor.php
│     
└── /www                                    # "publicly" accessible part of the webapp
    ├── /assets                             # static files
    │   ├── /css                            # CSS - styling
    │   │   ├── admin.css
    │   │   ├── editor.css
    │   │   ├── header_footer.css
    │   │   ├── homepage.css
    │   │   ├── message.css
    │   │   ├── news.css
    │   │   ├── style.css
    │   │   └── user.css
    │   │
    │   ├── /js                             # JS - client side logic
    │   │   ├── admin.js
    │   │   ├── dataValidation.js
    │   │   ├── editor.js
    │   │   ├── loadDataOnRefresh.js
    │   │   ├── messageDisplay.js
    │   │   ├── news.js
    │   │   ├── overlay.js
    │   │   ├── utils.js
    │   │   └── xhr.js
    │   │
    │   ├── /images                         # Images - web graphics, designs, etc..
    │   │   └── favicon.ico
    │   │
    │   └── /uploads                        # Files uploaded by users
    │       ├── /articles                   # Article images
    │       └── /profile_images             # Profile picutes of users
    │           └── _default.png     
    │
    ├── config.php                          # Configuration file
    ├── config.local.php                    # Local configuration file for tweaking
    └── index.php                           # Entering point of the webpage
```

**Database structure**
```
+---------------+        +---------------+
|    user       |        |   article     |
+---------------+        +---------------+
| id (PK)       |<-------| author_id (FK)|
| username      |        | id (PK)       |
| fullname      |        | title         |
| password      |        | subtitle      |
| profile_image |        | content       |
| role          |        | image_paths   |
| created_at    |        | slug          |
+---------------+        | created_at    |
                         +---------------+
```

**Purpose of Each Folder/File**:
- `Controllers/`: receives API or UI actions, performs corresponding logic, and interacts with models
- `Helpers/`: reusable utility scripts and functions that provide commonly used functionality
- `Logic/`: application-specific logic like complex objects or page routing
- `Models/`: contains the ORM and database interaction code
- `Views/`: deals with UI presentation, templating or frontend rendering


- `css/`: Frontend styles
- `js/`: Contains the frontend logic
- `images/`: Web graphics - logos etc..
- `uploads/`: All user uploaded images are located here



## Algorithm and Logic Design
Examples of algorithms with pseudocode and php snippets

**User registration**:
- All registration logic is maintained using RegisterController.php
```
1. Receive user input: username, password, ...
2. Validate the input:
   - Ensure all fields are provided
   - Check if each value is in the correct format
   - Verify if the password meets minimum strength requirements (e.g., length, special characters) and verify it with second confirmation password
3. Check if the username already exists in the database.
   - If exists, return an error message (e.g., "Username is already in use.")
4. Hash the password using a PHP password_hash() function
5. Insert the validated user data into the database
6. Return success with a welcome message and redirect to login
```
```php
    public function register(): void
    {
        try {
            $username = $_POST['username'] ?? null;
            $fullname = $_POST['fullname'] ?? null;
            $password = $_POST['password'] ?? null;
            $passConf = $_POST['password-confirm'] ?? null;
            $pfpImage = ImageHelper::getUsableImageArray($_FILES['image'])[0] ?? null;


            // Validate every input
            $this->validator->validateUsername($username);
            $this->validator->validateFullname($fullname);
            $this->validator->validatePassword($password, $passConf);

            if (isset($pfpImage)) {
                $this->validator->validateImage($pfpImage);
            }

            // Hash password
            $password = password_hash(
                password: $password,
                algo: PASSWORD_DEFAULT,
            );

            // Save image
            if (isset($pfpImage)) {
                $pfpImagePath = "assets/uploads/profile_images/$username.jpeg";
                ImageHelper::saveImage(
                    image: ImageHelper::processProfilePicture($pfpImage),
                    imagePath: $pfpImagePath,
                );
            }

            // Insert user into database
            UserModel::insertUser(
                username: $username,
                fullname: $fullname,
                password: $password,
                profile_image_path: $pfpImagePath ?? null,
            );

            // Redirect to login page
            Router::redirect(path: 'login', query: ['success' => 'register']);
        } catch (Exception $e) {
            Router::redirect(path: 'register', query: ['error' => $e->getMessage()]);
        }
    }
```

**User Login**
- All login logic is maintained using LoginController.php
```
1. Receive user input: username and password.
2. Validate the input:
    - Ensure all fields are provided
    - Check if each value is in the correct format
3. Query the database for a record that matches the provided username
    - If no record exists, return an error message (e.g., "Username or password are incorrect").
4. Use the hashed password stored in the database and verify it against the user-provided password
    - If the password is invalid, return an error message (e.g., "Invalid credentials.")
5. Generate a SESSION with user-specific information
6. Return a success response and redirect the user
```
```php
    public function login(): void
    {
        try {
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;

            // Validate if request contains a valid username and password, redirects if invalid.
            $this->validator->validatePassword($password, $password);

            // Retrieve user data from the database based on the username.
            $databaseData = UserModel::selectUser(username: $username);

            // Check if user exists in the database, redirects if not found.
            if (!$databaseData) {
                Router::redirect(path: 'login', query: ['error' => 'loginError']);
            }

            // Verify the provided username and password against the database credentials.
            $this->validator->validateUserCredentials(
                username: $username,
                databaseUsername: (string)$databaseData['username'],
                password: $password,
                databasePassword: (string)$databaseData['password'],
            );

            if (!isset($_SESSION)) {
                session_start();
            }

            // Set session
            $_SESSION['username'] = $username;
            $_SESSION['user_data'] = User::getUserByUsername($username);
            $_SESSION['valid'] = true;
            $_SESSION['timeout'] = time();

            Router::redirect(path: 'users/' . $username, query: ['success' => 'login']);
        } catch (Exception $e) {
            Router::redirect(path: 'login', query: ['error' => $e->getMessage()]);
        }
    }
```

**Add Article**
- All article manipulation is handeled by ArticleController.php
```
1. Authenticate the user:
   - Verify the user, ensure they are logged in and have high enough permission to add articles
2. Receive user input for the article:
   - Fields: title, content ...
3. Validate the input:
   - Ensure all required fields are filled (e.g., title, content, ...)
   - Check maximum character limits for fields (e.g., 255 characters for title)
   - Verify images are in a correct format, size and dimensions
4. Insert the article into the database:
   - Fields stored include title, subtitle, content, user_id (author), created_at
5. Return a success message to the user and redirect to the article's page
```
```php
    public function __construct(?string $action = '')
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $this->validator = new Validator();
        
        ...

        switch ($this->action) {
            ...
            case 'add':
            case 'edit':
                $privilegeRedirect->redirectUser();
                $this->page = $this->editorPage;
                break;
            ...
        }
    }

    public function addArticle(): void
    {
        try {
            // Get data from $_POST
            $title = $_POST['title'] ?? null;
            $subtitle = $_POST['subtitle'] ?? null;
            $content = $_POST['content'] ?? null;
            $author = $_POST['author'] ?? null;
            $images = ImageHelper::getUsableImageArray($_FILES['image']) ?? null;

            $this->validator->validateArticle(
                title: $title,
                subtitle: $subtitle,
                content: $content,
            );

            $slug = ReplaceHelper::getUrlFriendlyString($title);
            $articleId = DatabaseConnector::selectMaxId('article') + 1;

            if ($articleId === 1) {
                DatabaseConnector::resetAutoIncrement('article');
            }

            if (isset($images) and $images[0]['tmp_name'] !== '') {
                for ($i = 0; $i < count($images); $i++) {
                    // Generate thumbnail from first image
                    if ($i === 0) {
                        $thumbnailPath = 'assets/uploads/articles/' . $articleId . '_0_thumbnail.jpeg';
                        $imagePaths[] = $thumbnailPath;
                        ImageHelper::saveImage(
                            image: ImageHelper::resize(ImageHelper::processArticleImage($images[$i]), 360, 200),
                            imagePath: $thumbnailPath,
                        );
                    }

                    // Save image
                    $imagePath = 'assets/uploads/articles/' . $articleId . '_' . $i . '.jpeg';
                    $imagePaths[] = $imagePath; // Add to array
                    ImageHelper::saveImage(
                        image: ImageHelper::resize(ImageHelper::processArticleImage($images[$i]), 800, 450),
                        imagePath: $imagePath,
                    );
                }
            }

            ArticleModel::insertArticle(
                title: $title,
                subtitle: $subtitle,
                content: $content,
                imagePaths: $imagePaths ?? null,
                authorId: $author,
            );

            Router::redirect(path: "articles/$slug", query: ['success' => 'articleAdded']);
        } catch (Exception $e) {
            Router::redirect(path: 'articles/add', query: ['error' => $e->getMessage()]);
        }
    }
```

**Search & Sort function for users**
- Search and sort logic is managed by a specific controller that the user is searching in, in this case UserController.php
```
1. Authenticate the user:
   - verify the user, ensure they are logged in and have high enough permission to search for users
2. Receive the search query for the user - for example users/get?search=string&sort=id&sortDirection=asc&page=1
3. Query the database for the specified users
    - get all users matching the search & sort query
5. Return users data to user
```
```php
    public function __construct(?string $action = null)
    {
        ...
        $this->privilegeRedirect = new PrivilegeRedirect();
        $this->privilegeRedirect->redirectHost();
         
        ...
        
        switch ($this->action) {
            case 'get': 
                $this->privilegeRedirect->redirectEditor();
                $this->getUsers();
                break;
            ...
        }
    }
    
    public function getUsers(): void
    {
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $sortDirection = $_GET['sortDirection'] ?? null;
        $page = $_GET['page'] ?? 1;

        // Convert date format
        $search = DateHelper::ifPrettyConvertToISO($search);

        // Create query
        // Search, Sorting, Paging
        $conditions = ($search) ? "WHERE id LIKE '$search%' OR username LIKE '%$search%' OR fullname LIKE '%$search%' OR 
                                    role LIKE '%$search%' OR created_at LIKE '%$search%'" : "";
        $conditions .= ($sort) ? " ORDER BY $sort" : "";
        $conditions .= ($sortDirection) ? " $sortDirection" : "";
        $conditions .= ($page) ? " LIMIT 10 OFFSET " . ($page - 1) * 10 : "";

        // Get users
        try {
            $usersData = UserModel::selectUsers(
                conditions: $conditions,
            );

            if (!$usersData) {
                throw new Exception('No articles found');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }

        echo json_encode($usersData);
        exit();
    }
```



## Scripts
### Handling
- The index.php is an entry point into the webapp
- From there, the Router.php logic component takes care of redirecting each request to its specific script based on link and method
  - For example, when server receives request for `/news/` page, the router will redirect the request towards NewsController.php that will load up a news.php view and render it for the client
- Backend scripts: Include route handler, controllers, helpers, logic scripts and models
- Frontend scripts: Include reusable components, stylesheets, and JS files for UI interactions

### Backend Scripts
Backend scripts are divided based on functionality, ensuring a modular and maintainable codebase:
- **Controllers: ** Handle requests and logic
  - [AdminController.php](../src/Controllers/AdminController.php) - handle rendering of the admin dashboard
  - [ArticleController.php](../src/Controllers/ArticleController.php) - manages CRUD operations for articles, including adding or editing, provides AJAX-based data for admin dashboard and news page
  - [CommentController.php](../src/Controllers/CommentController.php) - manages operations for comments under articles
  - [Controller.php](../src/Controllers/Controller.php) - parent class
  - [ErrorController.php](../src/Controllers/ErrorController.php) - render errors
  - [HomepageController.php](../src/Controllers/HomepageController.php) - render homepage
  - [LoginController.php](../src/Controllers/LoginController.php) - renders login page and handles login logic
  - [NewsController.php](../src/Controllers/NewsController.php) - renders news page, stats and functionalities (e.g., article management, sorting, and search).
  - [RegisterController.php](../src/Controllers/RegisterController.php) - renders registration page and handles register logic
  - [UserController.php](../src/Controllers/UserController.php) for user-related actions, such as registration, login, or user removal, it also provides data via ajax

- **Helpers: ** Utilities and tools
  - [DateHelper.php](../src/Helpers/DateHelper.php) - convert dates between ISO and "pretty" formats
  - [ImageHelper.php](../src/Helpers/ImageHelper.php) - crop,resize and manipulate with photos
  - [PrivilegeRedirect.php](../src/Helpers/PrivilegeRedirect.php) - redirecting users based on their "rank" (owner,admin,user, ...)
  - [ReplaceHelper.php](../src/Helpers/ReplaceHelper.php) - manipulation with strings, used, for example, for creating slug from title
  - [UrlHelper.php](../src/Helpers/UrlHelper.php) - generating baseURL, implemented because of zwa.toad.cz server

- **Logic: ** Platform specific logic and routing
  - [Article.php](../src/Logic/Article.php) - article object
  - [DatabaseException.php](../src/Logic/DatabaseException.php) - custom database exception
  - [IncorrectInputException.php](../src/Logic/IncorrectInputException.php) - custom incorrect input exception
  - [Router.php](../src/Logic/Router.php) - routing of requests
  - [User.php](../src/Logic/User.php) - user object
  - [Validator.php](../src/Logic/Validator.php) - user input validation (e.g. username, fullname, images, titles, ...)

- **Models: ** Database logic
  - [ArticleModel.php](../src/Models/ArticleModel.php) - data manipulation of articles inside database
  - [DatabaseConnector.php](../src/Models/DatabaseConnector.php) - communication with database and templates for basic database functions
  - [UserModel.php](../src/Models/UserModel.php) - data manipulation of users inside database

### Frontend Scripts
Frontend scripts are divided to enhance reusability and interactivity:
- **Globally used scripts: **
  - [xhr.js](../www/assets/js/xhr.js) - component for getting data from server using AJAX
  - [messageDisplay.js](../www/assets/js/messageDisplay.js) - dynamically display error and success messages incoming either from server or client 
  - [dataValidation.js](../www/assets/js/dataValidation.js) - dynamically process and validate user input, uses communication with server using AJAX
  - [loadDataOnRefresh.js](../www/assets/js/loadDataOnRefresh.js) - load data into input fields when an incorrect form request is sent and the user is redirected back

- **Page-specific scripts:**
  - [admin.js](../www/assets/js/admin.js)
    - used for gathering data from server using AJAX - users and articles
      - handle search/sorting/paging requests
    - adds the overlay ability to items in tables
    - render results dynamically without refreshing the page
  - [editor.js](../www/assets/js/editor.js)
    - handles deleting article/user image via AJAX with server
    - updates the UI asynchronously
  - [imageSlideshow.js](../www/assets/js/article.js)
    - provides an ability to cycle through images on article page
  - [news.js](../www/assets/js/news.js)
    - dynamically fetches and appends news content from the backend
    - handle search/sorting/paging requests for articles
    - render results dynamically without refreshing the page
  - [overlay.js](../www/assets/js/overlay.js)
    - display overlay with more space for content it is displaying
  - [utils.js](../www/assets/js/utils.js)

- **Stylesheets:**
  - [header_footer.css](../www/assets/css/header_footer.css) - styling for header
  - [style.css](../www/assets/css/style.css) - globally set style variables, all other styles are imported into this style

    

## Features Explanation
More information on how to use the platform is [here](user_documentation.md)

- **User Registration & Login**:
  - user fills out the form
  - JS handles input validation (using `dataValidation.js`)
  - frontend:
    - sends an AJAX request to the backend with the payload: `{ username }` to check if username is free to use
  - backend:
    - validates the input data
    - authenticates user
    - responds with success/failure and redirection

- **Article Adding & Editing**
  - for editing the form comes pre-filled 
  - user fills out the form
  - JS handles input validation (using `dataValidation.js`)
  - frontend: 
    - sends an AJAX request to the backend with the payload: `{ title }` to check title existence
  - backend:
    - validates the input data
    - stores the article in the database
    - responds with success/failure and redirection

- **User Removal**
  - frontend: 
    - User clicks a "Remove" button in the admin panel
    - sends request with the user ID `{ id: 123 }` to delete user
  - backend:
    - verifies admin permissions
    - deletes the user from the database
    - returns a successful/fail deletion response
  - frontend reloads items

- **Remove Image via AJAX**
  - User clicks "Delete" on an image preview
  - Frontend:
    - Sends an AJAX request to the backend with the image path: `{ image: /assets/uploads/... }`.
  - Backend:
    - verifies permissions
    - deletes the image file from the server.
    - updates the database to remove the image reference.
    - returns success/error message
  - frontend:
    - updates the UI to remove the image preview or replace it with a placeholder

- **Admin Dashboard Data (AJAX)**
  - frontend:
    - sends AJAX GET requests to the backend to fetch users and articles
  - backend:
    - gathers data from the database and returns it as a JSON response
  - frontend displays data

- **Search and Sort Data (AJAX)**
  - frontend:
    - user types a query or clicks a table header/sorter-select for sorting
    - sends an AJAX GET request with the query or sorting parameters: `search`, `sort`, `sortDirection`, `page`
  - backend:
    - processes the request with a fuzzy `LIKE` SQL query for search and `ORDER BY` for sorting
    - returns paginated results as JSON
  - frontend updates the displayed list/table without reloading the entire page

- **News Display with AJAX**
  - frontend:
    - sends an AJAX GET request with the query or sorting parameters ...
  - backend:
    - processes the request with a fuzzy `LIKE` SQL query for search and `ORDER BY` for sorting
    - returns paginated results as JSON
  - frontend dynamically appends the articles to the news feed


## Configuration
**Configurations**:
- use `config.local.php`
- Set up the database connection using this template, if you want to, you can edit other properties of the server inside the local config
``` 
$_ENV['database'] = [
    'server' => 'localhost',
    'dbname' => 'vanekeri',
    'username' => 'vanekeri',
    'password' => 'petrpaveluwu',
];
```


## Troubleshooting
- **Issue: Database connection failed**
  - **Solution**: Check connection variables in `config.local.php`, ensure DB is running and can be accessed via other means, if everything fails, check if the database is properly structured, if not use the included database_scheme.sql

