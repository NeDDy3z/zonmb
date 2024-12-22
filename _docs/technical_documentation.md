# Technical Documentation

For more indepth technical documentation visit the auto-generated [documentation page](phpdoc/index.html)!


### Technologies

- PHP 8.2
- MySQL

## Project file structure
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
│   ├── /Models                             # logic for communication with database (User, Article, Comment...)
│   │   ├── User.php
│   │   ├── Article.php
│   │   └── Comment.php
│   │    
│   └── /Views                              # templates for individual pages (HTML)
│       ├── /Partials                       # page partials
│       │   ├── footer.php
│       │   └── header.php
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
└── /www                                    # "publicly" accessible part of website
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
    └── index.php                           # Entering point of the webpage
```

## Database scheme
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

## PHPDoc / PHPStan

- phpdoc: `php bin/phpDocumentor/phpDocumentor.phar run -d ./ -t _docs/phpdoc --ignore="bin/"`
- phpstan: `bin/phpstan/phpstan.phar -c bin/phpstan/phpstan.neon -d src`
