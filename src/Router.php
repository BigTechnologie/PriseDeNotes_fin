<?php
namespace App;

use App\Security\ForbiddenException;

class Router {

    /**
     * @var string
     */
    private $viewPath;

    /**
     * @var AltoRouter
     */
    private $router;

    public function __construct(string $viewPath) // Est le constructeur de la class, qui a pour parametre le chemin vers les vues
    {
        $this->viewPath = $viewPath;
        $this->router = new \AltoRouter(); // Tu stock dans la propriété routeur, une nouvelle instance de AltoRouter
    }

    public function get(string $url, string $view, ?string $name = null): self  // param: l'url, la vue qu'on souhaite chargée, le nom de notre route
    {
        $this->router->map('GET', $url, $view, $name);

        return $this;
    }

    public function post(string $url, string $view, ?string $name = null): self
    {
        $this->router->map('POST', $url, $view, $name);  // map en GET l'url(url appelée en GET), tu charges la vue et son nom

        return $this; // On renvoit l'objet en cours, ce qui permet de dire que le retour ça sera la class, d'ou self à la ligne 24
    }

    public function match(string $url, string $view, ?string $name = null): self
    {
        $this->router->map('POST|GET', $url, $view, $name);

        return $this;
    }

    //$name: Le nom de la route, $params = []: un tableau de parametres par defaut vide
    public function url (string $name, array $params = []) {
        //La méthode utilise un objet $router pour générer l'URL associée à la route spécifiée par le nom ($name). Les paramètres de la route sont fournis à partir du tableau $params.
        return $this->router->generate($name, $params);
    }


    /**
     * verification auprès du rooter si l'url taper correspond à une de mes routes
     * Contrairement à switch, la comparaison est une vérification d'identité (===) plutôt qu'un contrôle d'égalité faible (==)
     * 
    */
    public function run(): self
    {
        // Permet d'interroger le router pour savoir si l'url appelée correspond à une de ces routes
        $match = $this->router->match();
        // Prend la première valeur, et si elle n'existe pas tu prends la seconde. Je recupère la clé target, on recupère le template
        $view = $match['target'] ?: 'e404';
        //Cette partie extrait les paramètres associés à la correspondance. La clé 'params' est utilisée pour accéder à cette partie spécifique de la correspondance.
        $params = $match['params'];
        $router = $this; // Ce qu'on va renvoyé à la vue sera alors this, l'objet courant, mon router à moi
        $isAdmin = strpos($view, 'admin/') !== false;
        $layout = $isAdmin ? 'admin/layouts/default' : 'layouts/default';
        try {
            ob_start(); // Tu demarre la buferisation en sortie
            // Tu vas utiliser le viewPath, et tu iras charger le fichier de vue qui corresponds + .php
            require $this->viewPath . DIRECTORY_SEPARATOR . $view . '.php';
            $content = ob_get_clean(); // recuperation du contenu
            // On charge une autre vue. DIRECTORY_SEPARATOR: quelque soit l'environnement il va choisir le bon sépérateur
            require $this->viewPath . DIRECTORY_SEPARATOR . $layout . '.php';
        } catch (ForbiddenException $e) {
            // cette ligne envoie un en-tête HTTP de redirection (Location) vers l'URL associée à la route nommée 'login'
            //La méthode $this->url('login') est utilisée pour obtenir l'URL de la route de connexion.
            //'?forbidden=1' est ajoutée, ce qui peut être utilisé pour transmettre des informations supplémentaires à la page de connexion (par exemple, pour afficher un message d'erreur).
            header('Location: ' . $this->url('login') . '?forbidden=1');
            //utilisée pour arrêter immédiatement l'exécution du script après la redirection. Cela garantit que le reste du code ne sera pas exécuté après la redirection.
            exit();
        }
         //La méthode renvoie l'instance actuelle de la classe.
        return $this;
    }

}