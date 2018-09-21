<?php
namespace Puzzle\Admin\ExpertiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceCreateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\ServiceUpdateType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\StaffCreateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Puzzle\Admin\ExpertiseBundle\Form\Type\StaffUpdateType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class StaffController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['fullName', 'position', 'description', 'contacts', 'ranking'];
    }
    
	/***
	 * List staffs
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request) {
		try {
		    /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
		    $apiClient = $this->get('puzzle_connect.api_client');
		    $staffs = $apiClient->pull('/expertise/projects', null);
		}catch (BadResponseException $e) {
		    /** @var EventDispatcher $dispatcher */
		    $dispatcher = $this->get('event_dispatcher');
		    $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
		    $staffs = [];
		}
		
		return $this->render("PuzzleAdminExpertiseBundle:Staff:list.html.twig",['staffs' => $staffs]);
	}
	
    /***
     * Create a new staff
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $data = PuzzleApiObjectManager::hydratate($this->fields, ['ranking' => 1]);
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $services = [];
        
        try {
            $items = $apiClient->pull('/expertise/services', ['fields' => 'title,id']);
            foreach ($items as $item) {
                $services[$item['title']] = $item['id'];
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
        }
        
        $form = $this->createForm(StaffCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_staff_create')
        ]);
        $form->add('service', ChoiceType::class, array(
            'translation_domain' => 'admin',
            'label' => 'expertise.staff.service',
            'label_attr' => ['class' => 'form-label'],
            'choices' => $services,
            'attr' => ['class' => 'select'],
        ));
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
            $uploader = $this->get('admin.media.upload_manager');
            $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
            
            $postData = $form->getData();
            $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
            $postData['contacts'] = $postData['contacts'] ? explode(',', $postData['contacts']) : null;
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $staff = $apiClient->push('post', '/expertise/staffs', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
                
                return $this->redirectToRoute('admin_expertise_staff_update', array('id' => $staff['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                if ($request->isXmlHttpRequest() === true) {
                    return $event->getResponse();
                }
                
                return $this->redirectToRoute('admin_expertise_staff_list');
            }
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Staff:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show staff
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $staff = $apiClient->pull('/expertise/staffs/'.$id);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $staff = [];
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Staff:show.html.twig", array(
            'staff' => $staff
        ));
    }
    
    
    /***
     * Update staff
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        $data = PuzzleApiObjectManager::hydratate($this->fields, []);
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $services = $data = [];
        
        try {
            $staff = $apiClient->pull('/expertise/staffs/'.$id);
            $data = PuzzleApiObjectManager::hydratate($this->fields, $staff);
            $data['service'] = $staff['_embedded']['service']['id'];
            
            $items = $apiClient->pull('/expertise/services', ['fields' => 'title,id']);
            foreach ($items as $item) {
                $services[$item['title']] = $item['id'];
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $staff = $services = [];
        }
        
        $form = $this->createForm(StaffUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_expertise_staff_update', ['id' => $id])
        ]);
        $form->add('service', ChoiceType::class, array(
            'translation_domain' => 'admin',
            'label' => 'expertise.staff.service',
            'label_attr' => ['class' => 'form-label'],
            'choices' => $services,
            'data' => $data['service'],
            'attr' => ['class' => 'select'],
        ));
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            /** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
            $uploader = $this->get('admin.media.upload_manager');
            $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
            
            $postData = $form->getData();
            $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
            
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $apiClient->push('put', '/expertise/staffs/'.$staff['id'], $postData);
            
            return $this->redirectToRoute('admin_expertise_staff_list');
        }
        
        return $this->render("PuzzleAdminExpertiseBundle:Staff:update.html.twig", [
            'staff' => $staff,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Remove staff
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_EXPERTISE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
           
        	$response = $apiClient->push('delete', '/expertise/staffs/'.$id);
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
        
        return $this->redirectToRoute('admin_expertise_staff_list');
    }
}
