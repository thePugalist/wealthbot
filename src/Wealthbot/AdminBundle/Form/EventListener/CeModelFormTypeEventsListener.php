<?php

namespace Wealthbot\AdminBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Wealthbot\AdminBundle\Entity\CeModel;

class CeModelFormTypeEventsListener implements EventSubscriberInterface
{
    /** @var FormFactory */
    private $factory;

    /** @var EntityManager */
    private $em;

    public function __construct(FormFactoryInterface $factory, EntityManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        /** @var $data CeModel */
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }
        // check if the product object is not "new"
        if ($data->getId()) {

            //$modelsCount = $this->em->getRepository('WealthbotAdminBundle:CeModel')->getModelsCountByParentIdAndOwnerId($data->getParent()->getId(), $data->getOwnerId());

            $form->add($this->factory->createNamed('risk_rating', 'choice', $data->getRiskRating(), [
                'placeholder' => 'Select Risk Rating',
                'choices' => array_combine(range(1, 100), range(1, 100)),
                'mapped' => false,
                'auto_initialize' => false,
            ]));
        }
    }

    public function bind(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var $data CeModel */
        $data = $event->getData();

        if ($data) {
            if ($form->has('risk_rating')) {
                $riskRating = $form->get('risk_rating')->getData();

                $isExistRiskRating = $this->em->getRepository('WealthbotAdminBundle:CeModel')->isExistRiskRating(
                    $data->getParent()->getId(),
                    $data->getOwnerId(),
                    $data->getRiskRating(),
                    $data->getId()
                );

                if ($isExistRiskRating) {
                    $form->get('risk_rating')->addError(new FormError('The risk with parameter :risk is already exists.', [':risk' => $riskRating]));
                }
            }
        }
    }
}
