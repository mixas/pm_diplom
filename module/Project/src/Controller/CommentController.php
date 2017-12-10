<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
use User\Entity\User;
use Project\Form\CommentForm;

/**
 * Контроллер для обработки запросов связанных с комемнтариями
 */
class CommentController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Comment manager.
     * @var Project\Service\CommentManager
     */
    private $commentManager;


    private $authService;

    public function __construct($entityManager, $commentManager, $rendererInterface, $authService)
    {
        $this->entityManager = $entityManager;
        $this->commentManager = $commentManager;
        $this->rendererInterface = $rendererInterface;
        $this->authService = $authService;
    }

    /**
     * Добавление коментария (ajax действие)
     *
     * @return \Zend\Http\Response|\Zend\Stdlib\ResponseInterface
     */
    public function addAction()
    {
        $response = $this->getResponse();

        $taskId = (int)$this->params()->fromRoute('id', -1);
        if ($taskId < 1) {
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Task was not found')));
            return $response;
        }

        $task = $this->entityManager->getRepository(Task::class)
            ->find($taskId);

        $project = $task->getProject();

        // Проверка полномочий
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Создание формы
        $form = new CommentForm('create', $this->entityManager, null, null);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Считывание данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Блок добавления комментария в БД
                try {
                    $currentUser = $this->entityManager->getRepository(User::class)
                        ->findOneByEmail($this->authService->getIdentity());

                    $comment = $this->commentManager->addComment($data, $task, $currentUser);

                    $viewModel = new ViewModel(
                        ['comment' => $comment]
                    );
                    $viewModel->setTemplate('comment');
                    $renderer = $this->rendererInterface;
                    $html = $renderer->render($viewModel);

                    $response->setContent(\Zend\Json\Json::encode(
                        array(
                            'response' => true,
                            'message' => 'Your comment has been successfully added',
                            'html' => $html
                        )
                    ));
                }catch (\Exception $e){
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
                }

            }else{
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid data. Please try again')));
            }
        }else{
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid request. Please try again')));
        }

        return $response;
    }

    /**
     * Редактирование существующего комментария
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function editAction()
    {
        $response = $this->getResponse();

        $commentId = (int)$this->params()->fromRoute('id', -1);
        if ($commentId < 1) {
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Comment was not found')));
            return $response;
        }

        $form = new CommentForm('create', $this->entityManager, null, null);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Считывание данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                try {
                    $data = $form->getData();

                    // Поиск комментария по ID
                    $comment = $this->entityManager->getRepository(Comment::class)
                        ->find($commentId);

                    if (!$comment->getId()) {
                        $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Comment was not found')));
                        return $response;
                    }

                    if (!$this->access('comments.manage.own', ['comment' => $comment])) {
                        $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'You are not allowed to manage this comment')));
                        return $response;
                    }

                    // Обновление комментария в БД
                    $this->commentManager->updateComment($comment, $data);

                    $viewModel = new ViewModel(
                        ['comment' => $comment]
                    );
                    $viewModel->setTemplate('comment');
                    $renderer = $this->rendererInterface;
                    $html = $renderer->render($viewModel);

                    $response->setContent(\Zend\Json\Json::encode(
                        array(
                            'response' => true,
                            'message' => 'Your comment has been successfully updated',
                            'html' => $html
                        )
                    ));
                }catch (\Exception $e){
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
                }
            }else{
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid data. Please try again.')));
            }
        } else {
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid Request')));
        }

        return $response;
    }

}


