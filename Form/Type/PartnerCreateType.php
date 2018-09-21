<?php
namespace Puzzle\Admin\ExpertiseBundle\Form\Type;

use Puzzle\Admin\ExpertiseBundle\Form\Model\AbstractPartnerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class PartnerCreateType extends AbstractPartnerType
{
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		
		$resolver->setDefault('csrf_token_id', 'partner_create');
		$resolver->setDefault('validation_groups', ['Create']);
	}
	
	public function getBlockPrefix() {
		return 'admin_expertise_partner_create';
	}
}
