<?php
$View = new View(new Path(), 'assets/views/svg.php');
$View->setVars([
    'icon' => 'logo'
]);
echo $View->output();
?>

<!-- OR -->

<a class="c-icon c-icon--logo" title="" href="#">
    <?php
    $View = new View(new Path(), 'assets/views/svg.php');
    $View->setVars([
        'icon'    => 'logo',
        'classes' => 'c-icon__img'
    ]);
    echo $View->output();
    ?>
</a>
