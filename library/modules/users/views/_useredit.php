<?php
use usni\library\utils\TimezoneUtil;
use usni\library\modules\users\utils\UserUtil;

$model   = $formDTO->getModel();

?>

<?= $form->field($model, 'username')->textInput();?>
<?= $form->field($model, 'status')->select2input(UserUtil::getStatusDropdown());?>
<?= $form->field($model, 'status')->select2input(UserUtil::getCustomerTypeDropdown());?>
<?= $form->field($model, 'timezone')->select2input(TimezoneUtil::getTimezoneSelectOptions());?>
<?= $form->field($model, 'groups')->select2input($formDTO->getGroups(), true, ['multiple'=>'multiple']);?>
<?php
if($model->scenario == 'create' || $model->scenario == 'registration')
{
?>
    <?= $form->field($model, 'password')->passwordInput();?>
    <?= $form->field($model, 'confirmPassword')->passwordInput();?>
<?php
}
?>
<?= $form->field($model,'type')->hiddenInput(['value' => 'system'])->label(false);?>