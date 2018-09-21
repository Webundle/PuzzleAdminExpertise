<?php
namespace Puzzle\Admin\ExpertiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceUpdateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ProjectCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ProjectUpdateType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;
use Puzzle\ConnectBundle\Service\ErrorFactory;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class ProjectController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['title', 'service', 'description', 'location', 'client', 'startedAt', 'endedAt'];
    }
    
	/***
	 * List projects
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request){
		try {
    		/** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
    		$apiClient = $this->get('puzzle_connect.api_client');
    		$projects = $apiClient->pull('/expertise/projects', null);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $projects = [];
        }
		
		return $this->render("PuzzleAdminExpertiseBundle:Project:list.html.twig",['projects' => $projects]);
	}
	
    /***
     * Create a new project
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $serviceId = $request->query->get('parent');
        $data = PuzzleApiObjectManager::hydratate($this->fields, ['service' => $serviceId]);
        
        $form = $this->createForm(ProjectCreateType::class, $data, [
            'method' => 'POST',
            'action' => ! $serviceId ? $this->generateUrl('admin_expertise_project_create') : 
                                        $this->generateUrl('admin_expertise_project_create', ['service' => $serviceId])
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                $postData = $form->getData();
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $project = $apiClient->push('post', '/expertise/projects', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_project_update', array('id' => $project['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Project:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show project
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $project = $apiClient->pull('/expertise/projects/'.$id);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $project = [];
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Project:show.html.twig", array(
            'project' => $project
        ));
    }
    
    /***
     * Update project
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $data = [];
        
        try {
            $project = $apiClient->pull('/expertise/projects/'.$id);
            $serviceId = $project['_embedded']['parent']['id'] ?? null;
            
            $data = PuzzleApiObjectManager::hydratate($this->fields, $project);
            $data['service'] = $serviceId;
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $project = [];
        }
        
        $form = $this->createForm(ProjectUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_project_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
                $postData = $form->getData();
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $project = $apiClient->push('put', '/expertise/projects/'.$id, $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_project_update', array('id' => $project['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Project:update.html.twig", [
            'project' => $project,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Remove project
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $response = $apiClient->push('delete', '/expertise/projects/'.$id);
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse($response);
            }
            
            $this->addFlash('success', $this->get('translator')->trans('message.delete', [], 'success'));
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event  = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            
            if ($request->isXmlHttpRequest()) {
                return $event->getResponse();
            }
        }
        
        return $this->redirectToRoute('admin_expertise_project_list');
    }
}
