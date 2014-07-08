<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Entity\Category;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_post_set_data_and_post_submit_form_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                FormEvents::PRE_SET_DATA  => 'preSetData',
                FormEvents::POST_SET_DATA => 'postSetData',
                FormEvents::POST_SUBMIT   => 'postSubmit'
            ]
        );
    }

    function it_adds_permissions_to_the_form(FormEvent $event, Form $form, Category $category)
    {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $form->add('permissions', 'pimee_enrich_category_permissions')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_roles_in_the_form_data(
        FormEvent $event,
        Category $category,
        Form $form,
        Form $viewForm,
        Form $editForm,
        $accessManager
    ) {
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);

        $accessManager->getViewRoles($category)->willReturn(['foo', 'bar', 'baz']);
        $accessManager->getEditRoles($category)->willReturn(['bar', 'baz']);

        $viewForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_the_selected_permissions_if_the_form_is_valid(
        FormEvent $event,
        Category $category,
        Form $form,
        Form $viewForm,
        Form $editForm,
        Form $applyForm,
        $accessManager
    ) {
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(true);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);
        $form->get('apply_on_children')->willReturn($applyForm);

        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);
        $applyForm->getData()->willReturn(false);

        $accessManager->setAccess($category, ['one', 'two'], ['three'])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_applies_the_new_permissions_on_children(
        FormEvent $event,
        Category $category,
        Form $form,
        Form $viewForm,
        Form $editForm,
        Form $applyForm,
        $accessManager
    ) {
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(true);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);
        $form->get('apply_on_children')->willReturn($applyForm);

        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);
        $applyForm->getData()->willReturn(true);

        $accessManager->setAccess($category, ['one', 'two'], ['three'])->shouldBeCalled();
        $accessManager->addChildrenAccess($category, ['one', 'two'], ['three'], [], [])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_the_form_is_invalid(
        FormEvent $event,
        Category $category,
        Form $form,
        $accessManager
    ) {
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(false);

        $accessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
