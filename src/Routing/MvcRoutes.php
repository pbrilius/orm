<?php

declare(strict_types=1);

namespace App\Routing;

use App\Controller\UserController;
use App\Http\Router;
use App\Http\Request;
use App\Http\Response;
use App\View\ViewRenderer;
use Doctrine\ORM\EntityManager;

class MvcRoutes
{
    private Router $router;
    private array $controllers;
    private ViewRenderer $view;

    public function __construct(EntityManager $em)
    {
        $this->router = new Router();
        $this->view = new ViewRenderer();
        $this->controllers = [
            'user' => new UserController($em),
        ];
        $this->register();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    private function register(): void
    {
        $this->router->get('/', function (Request $req) {
            return new Response($this->view->render('home', [
                'title' => 'Oryx ORM - MVC Application',
            ]));
        });

        $this->router->get('/users', function (Request $req) {
            $data = $this->controllers['user']->index();
            return new Response($this->view->render('users/index', $data));
        });

        $this->router->get('/users/create', function (Request $req) {
            return new Response($this->view->render('users/create'));
        });

        $this->router->post('/users/create', function (Request $req) {
            $data = $req->all();
            if (!empty($data['email']) && !empty($data['password'])) {
                $this->controllers['user']->create($data);
                header('Location: /users');
                exit;
            }
            header('Location: /users/create');
            exit;
        });

        $this->router->get('/users/{id}', function (Request $req, array $params) {
            $user = $this->controllers['user']->show((int) $params['id']);
            if (!$user) {
                return new Response($this->view->render('error/404', [
                    'message' => 'User not found',
                ]), 404);
            }
            return new Response($this->view->render('users/show', ['user' => $user]));
        });

        $this->router->get('/users/{id}/edit', function (Request $req, array $params) {
            return new Response($this->view->render('users/edit', [
                'id' => $params['id'],
            ]));
        });

        $this->router->post('/users/{id}/delete', function (Request $req, array $params) {
            $this->controllers['user']->delete((int) $params['id']);
            header('Location: /users');
            exit;
        });
    }
}
