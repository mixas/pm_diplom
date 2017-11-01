<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
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

    /**
     * Constructor.
     */
    public function __construct($entityManager, $commentManager, $rendererInterface)
    {
        $this->entityManager = $entityManager;
        $this->commentManager = $commentManager;
        $this->rendererInterface = $rendererInterface;
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
                    $comment = $this->commentManager->addComment($data, $task);

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
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Create task form
        $form = new TaskForm('update', $this->entityManager, $task, $this->taskManager);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update the user.
                $this->taskManager->updateTask($task, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('tasks',
                    ['action'=>'view', 'id'=>$task->getId()]);
            }
        } else {
            $form->setData(array(
                'assigned_user_id'=>$task->getAssignedUserId(),
                'estimate'=>$task->getEstimate(),
                'task_title'=>$task->getTaskTitle(),
                'status'=>$task->getStatus(),
                'description'=>$task->getDescription(),
            ));
        }

        return new ViewModel(array(
            'task' => $task,
            'form' => $form
        ));
    }

}


