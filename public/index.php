<?php
require '../vendor/autoload.php';

define('DEBUG_TIME', microtime(true));

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

if(isset($_GET['page']) && $_GET['page'] === '1') {  // Si les 2 cas sont verifié , on peut donc mettre en place notre systeme de redirection
    // On veut réecrire l'URL sans le parametre ?page. Pour ça on se base sur l'URL originale. On explose par le ? dans l'URL 
    $uri = explode('?', $_SERVER['REQUEST_URI'])[0]; // Permet de separer la partie qui contient les parametres et celle qui contient l'url. [0]:
    $get = $_GET;                                    //  car on est interessé que par la première partie
    unset($get['page']);  // Il faut que tu retires de ce tableau la clé 'page'
    // http_build_query: permet de génère une chaîne de requête en encodage URL. Permet de construire la partie de droite contenant les paramètres
    $query = http_build_query($get);
    if (!empty($query)) {
        $uri = $uri . '?' . $query; // Si $query est vide-> On ne touche pas l'url, si non on prend une nouvelle url->on rajoute un ? puis $query
    }
    http_response_code(301); // Je redirige cet utilisateur vers cette url
    header('Location: ' . $uri); // Redirection permanente 
    exit();
}

$router = new App\Router(dirname(__DIR__) . '/views');
$router
    ->get('/', 'post/index', 'home')
    ->get('/blog/category/[*:slug]-[i:id]', 'category/show', 'category')
    ->get('/blog/[*:slug]-[i:id]', 'post/show', 'post')
    ->match('/login', 'auth/login', 'login')    // la route qui mène vers la page de connexion
    ->post('/logout', 'auth/logout', 'logout')  // la route qui mène vers la page de deconnexion
    // ADMIN
    // Gestion des articles
    ->get('/admin', 'admin/post/index', 'admin_posts')
    ->match('/admin/post/[i:id]', 'admin/post/edit', 'admin_post')
    ->post('/admin/post/[i:id]/delete', 'admin/post/delete', 'admin_post_delete')
    ->match('/admin/post/new', 'admin/post/new', 'admin_post_new')
    // Gestion des catégories
    ->get('/admin/categories', 'admin/category/index', 'admin_categories')
    ->match('/admin/category/[i:id]', 'admin/category/edit', 'admin_category')
    ->post('/admin/category/[i:id]/delete', 'admin/category/delete', 'admin_category_delete')
    ->match('/admin/category/new', 'admin/category/new', 'admin_category_new')
    ->run();
