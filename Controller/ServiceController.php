<?php
namespace Puzzle\Admin\ExpertiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceUpdateType;
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
class ServiceController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['name', 'parent', 'description', 'classIcon', 'ranking'];
    }
    
	/***
	 * List services
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request, $current = "NULL") {
        try {
            $criteria = [];
            $criteria['filter'] = 'parent=='.$current;
            
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            
            $services = $apiClient->pull('/expertise/services', $criteria);
            $currentService = $current != "NULL" ? $apiClient->pull('/expertise/services/'.$current) : null;
            
            if ($currentService && isset($currentService['_embedded']) && isset($currentService['_embedded']['parent'])) {
                $parent = $currentService['_embedded']['parent'];
            }else {
                $parent = null;
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $services = $parent = $currentService = [];
        }
		
		return $this->render("PuzzleAdminExpertiseBundle:Service:list.html.twig",[
		    'services'      => $services,
		    'currentService' => $currentService,
		    'parent'          => $parent
		]);
	}
	
    /***
     * Create a new service
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $parentId = $request->query->get('parent');
        $data = PuzzleApiObjectManager::hydratate($this->fields, ['ranking' => 1]);
        
        $form = $this->createForm(ServiceCreateType::class, $data, [
            'method' => 'POST',
            'action' => !$parentId ? $this->generateUrl('admin_expertise_service_create') : 
                                     $this->generateUrl('admin_expertise_service_create', ['parent' => $parentId])
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
            $uploader = $this->get('admin.media.upload_manager');
            $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
            
            $postData = $form->getData();
            $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                $postData = $form->getData();
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $project = $apiClient->push('post', '/expertise/services', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_service_update', array('id' => $project['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Service:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show service
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $service = $apiClient->pull('/expertise/services/'.$id);
            $childs = $apiClient->pull('/expertise/services/'.['filter' => 'parent=='. $id]);
            
            $parent = null;
            if (isset($service['_embedded'])) {
                $parent = $service['_embedded']['parent'] ?? null;
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $service = $parent = $childs = [];
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Service:show.html.twig", array(
            'service' => $service,
            'parent' => $parent,
            'childs' => $childs
        ));
    }
    
    /***
     * Update service
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $service = $apiClient->pull('/expertise/services/'.$id);
        
        $parentId = $service['_embedded']['parent']['id'] ?? null;
        $data = [
            'title'  => $service['title'],
            'parent' => $parentId,
            'description' => $service['description'],
            'classIcon' => $service['classIcon'],
            'ranking' => $service['ranking']
        ];
        
        $form = $this->createForm(ServiceUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_service_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
            $uploader = $this->get('admin.media.upload_manager');
            $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
            
            $postData = $form->getData();
            $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                $postData = $form->getData();
                $postData = PuzzleApiObjectManager::sanitize($postData);
                
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $apiClient->push('put', '/expertise/services/'.$id, $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_service_update', array('id' => $id));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Service:update.html.twig", [
            'service' => $service,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Remove service
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $response = $apiClient->push('delete', '/expertise/services/'.$id);
            
            if ($parentId = $request->query->get('parent')) {
                $route = $this->redirectToRoute('admin_expertise_service_show', array('id' => $parentId));
        	}else{
        		$route = $this->redirectToRoute('admin_expertise_service_list');
        	}
        	
        	if ($request->isXmlHttpRequest()) {
        	    return new JsonResponse($response);
        	}
        	
        	$this->addFlash('success', $this->get('translator')->trans('message.delete', [], 'success'));
        	
        	return $route;
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event  = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            
            if ($request->isXmlHttpRequest()) {
                return $event->getResponse();
            }
            
            return $this->redirectToRoute('admin_expertise_project_list');
        }
    }
}
