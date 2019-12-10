<?php
// Example of how to use the workflow on Yii based on MVC, this particular case shows the action of create a new user.

// The controller, this function it’s called when the user use the register option.
public function actionRegister() {
  // An object of the model class User it's created in blank
  $modelRegister = new User;

  // In case that it's receiving data, the function has to continue with the registration process
  if(isset($_POST['user'])) {
    // Set the attributes of the object created before with the ones coming in the post
    $modelRegister->attributes = $_POST['user'];

    // Here the password it's check and in case it's not blank then it's encrypted
    // This validation it’s also checked latter but on this case it’s
    // to spare the encrypt process if isn’t necessary
    if($modelRegister->password != "") {
      $modelRegister->password = md5($modelRegister->password);
      $modelRegister->password_repeat = md5($modelRegister->password_repeat);
    }

    // The save() it's the one who validate all the data and save the information
    if($modelRegister->save()) {
      // An object identity it's created to have the section data
      $identity = new UserIdentity($modelRegister->username, $modelRegister->password);

      // The duration it's set to 30 days
      $duration = false ? 3600 * 24 * 30 : 0;

      // The login function it's called passing the identity object and the duration
      Yii::app()->user->login($identity, $duration);

      // The navigation it's redirected to the home page
      $this->redirect(Yii::app()->request->baseUrl);
    }
  }

  // In case that no data it's coming then it simply render the register view,
  // witch contains the form to register, sending the User object

  $this->render('register', array('modelRegister' => $modelRegister));
}

// Register Form
<div class="form">
  <?php
    $form = $this->beginWidget('booster.widgets.TbActiveForm', array('id' => 'user-form', 'type' => 'vertical', 'enableClientValidation' => true));
    echo $form->errorSummary($modelRegister);
  ?>

  <div class="row-fluid">
    <div class="col-lg-6 col-lg-offset-3">
      <?php echo $form->textFieldGroup($modelRegister, 'username', array('maxlength'=>20)); ?>
    </div>
  </div>
  <div class="row-fluid">
    <div class="col-lg-6 col-lg-offset-3">
      <?php echo $form->passwordFieldGroup($modelRegister, 'password', array('maxlength'=>50)); ?>
    </div>
  </div>
  <div class="row-fluid">
    <div class="col-lg-6 col-lg-offset-3">
      <?php echo $form->passwordFieldGroup($modelRegister, 'password_repeat', array('maxlength'=>50)); ?>
    </div>
  </div>
  <div class="row-fluid">
    <div class="col-lg-6 col-lg-offset-3">
      <?php echo $form->textFieldGroup($modelRegister, 'email', array('maxlength'=>50)); ?>
    </div>
  </div>
  <div class="row-fluid">
    <div class="col-lg-6 col-lg-offset-3">
      <?php echo $form->textFieldGroup($modelRegister, 'twitter', array('maxlength'=>100)); ?>
    </div>
  </div>
  <div class="row buttons">
    <div class="col-lg-6 col-lg-offset-3">
      <?php $this->widget('booster.widgets.TbButton', array('id'=>'buttonRegisterForm', 'label'=>'Register', 'buttonType'=>'submit')); ?>
    </div>
  </div>
  <?php $this->endWidget(); ?>
</div>
