<!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- <title inertia><?php echo e(config('app.name', 'Laravel')); ?></title> -->
        <title>MARKETMIS</title>

        <!-- Fonts -->
        <link rel="icon" href="<?php echo e(asset('images/marketIS-logo.ico')); ?>" type="image/x-icon">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
        <link
            rel="stylesheet"
            href="//cdn.jsdelivr.net/npm/element-plus/dist/index.css"
        />
        <!-- Import Vue 3 -->
        <script src="//cdn.jsdelivr.net/npm/vue@3"></script>
        <!-- Import component library -->
        <script src="//cdn.jsdelivr.net/npm/element-plus"></script>
        <!-- Scripts -->
        <?php echo app('Tighten\Ziggy\BladeRouteGenerator')->generate(); ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"]); ?>
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
    </head>
    <body class="font-sans antialiased">
        <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>
    </body>
</html>
<?php /**PATH /home/jolems/project/2025/marketMISproject/resources/views/app.blade.php ENDPATH**/ ?>