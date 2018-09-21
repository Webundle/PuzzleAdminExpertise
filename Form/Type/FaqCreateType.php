<?php
namespace Puzzle\Admin\ExpertiseBundle\Form\Type;

use Puzzle\Admin\ExpertiseBundle\Form\Model\AbstractFaqType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class FaqCreateType extends AbstractFaqType
{
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		
		$resolver->setDefault('csrf_token_id', 'faq_create');
		$resolver->setDefault('validation_groups', ['Create']);
	}
	
	public function getBlockPrefix() {
		return 'admin_expertise_faq_create';
	}
}
