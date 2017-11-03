<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
use User\Entity\User;
use Project\Entity\Project;
use Project\Service\TaskManager;
use Project\Form\CommentForm;
//use User\Form\PasswordChangeForm;
//use User\Form\PasswordResetForm;

/**
 * This controller is responsible for user management (adding, editing,
 * viewing users and changing user's password).
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

    /**
     * Constructor.
     */
    public function __construct($entityManager, $commentManager, $rendererInterface, $authService)
    {
        $this->entityManager = $entityManager;
        $this->commentManager = $commentManager;
        $this->rendererInterface = $rendererInterface;
        $this->authService = $authService;
    }

    /**
     * This is the default "index" action of the controller. It displays the
     * list of users.
     */
    public function indexAction()
    {
        $comments = $this->entityManager->getRepository(Comment::class)
            ->findBy([], ['id'=>'ASC']);

        return new ViewModel([
            'tasks' => $comments
        ]);
    }

    /**
     * This action displays a page allowing to add a new user.
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

        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Create user form
        $form = new CommentForm('create', $this->entityManager, null, null);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Add comment.
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

                    //todo: russian translation should be here but json can't encode russian characters.
                    $response->setContent(\Zend\Json\Json::encode(
                        array(
                            'response' => true,
                            'message' => 'Your comment has been successfully added',
                            'comment_html' => $html
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
     * The "edit" action displays a page allowing to edit user.
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

        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                try {
                    // Get filtered and validated data
                    $data = $form->getData();

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

                    // Update the user.
                    $this->commentManager->updateComment($comment, $data);

                    $viewModel = new ViewModel(
                        ['comment' => $comment]
                    );
                    $viewModel->setTemplate('comment');
                    $renderer = $this->rendererInterface;
                    $html = $renderer->render($viewModel);

                    //todo: russian translation should be here but json can't encode russian characters.
                    $response->setContent(\Zend\Json\Json::encode(
                        array(
                            'response' => true,
                            'message' => 'Your comment has been successfully updated',
                            'comment_html' => $html
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


