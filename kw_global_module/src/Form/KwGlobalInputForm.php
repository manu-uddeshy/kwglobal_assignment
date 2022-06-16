<?php

namespace Drupal\kw_global_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Provides a form for log & view data.
 */
class KwGlobalInputForm extends FormBase
{

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a logger object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, MessengerInterface $messenger)
  {
    $this->loggerFactory = $logger_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('logger.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'watchdog_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['user_name'] = [
      '#title' => $this->t('Enter Your First Name'),
      '#type' => 'textfield',
      '#placeholder' => $this->t('Enter First Name'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {

    if (!preg_match("/^([a-zA-Z']+)$/", $form_state->getValue('user_name'))) {
      $form_state->setErrorByName("user_name", $this->t('Please enter a valid name'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $user_name = !empty($form_state->getValue('user_name')) ? $form_state->getValue('user_name') : NULL;

    // drupal message
    $this->messenger->addMessage("You Just Submitted: " . $user_name);

    // passing the input value in logs using dependency injection.
    $this->loggerFactory->get('kwglobal_module')->notice($this->t('Submitted Name: %name', ['%name' => $user_name]));
  }
}
