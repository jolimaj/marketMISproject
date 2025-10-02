<?php $__env->startComponent('mail::message'); ?>
# Welcome to <?php echo e(config('app.name')); ?>!

Please click the button below to verify your email address and activate your account.

<?php $__env->startComponent('mail::button', ['url' => $actionUrl]); ?>
Verify Email
<?php echo $__env->renderComponent(); ?>

If you did not create an account, no further action is required.

Thanks,  
The <?php echo e(config('app.name')); ?> Team
<?php echo $__env->renderComponent(); ?>
<?php /**PATH /home/jolems/project/2025/marketMISproject/resources/views/verify-email.blade.php ENDPATH**/ ?>