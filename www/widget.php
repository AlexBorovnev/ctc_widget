<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="description" content="">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
    <script src="js/jquery.movingboxes.js"></script>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/movingboxes.css" rel="stylesheet">
</head>
<body>
<div class="logo">
    <span><img src="images/logo.png" alt=""></span>
</div>
<div id="promo_slider" class="promo_slider">
    <?php  $widgets = $widget->getWidget($widgetsId);
    foreach ($widgets as $key => $widget):?>
        <div class="<?php if (count($widgets) <= 3) echo 'widget';?> widget_<?= $widget['id'] ?>">
            <a href="<?= $widget['url']; ?>"><img class="offer_img"
                                                  src="picture.php?picture_custom=<?= $widget['picture']; ?>&picture_id=<?= $widget['picture_our_src']; ?>"
                                                  alt="" ></a>
            <div>
                <span class="price_text">
                    <span class="int_val"><?= $widget['price']['viewPrice']['intValue'] ?></span>
                    <span class="float_val"><?= $widget['price']['viewPrice']['floatValue'] ?></span>
                    руб
                </span>
            </div>
            <a class="button_sell" href="<?= $widget['url']; ?>"><img src="images/sell_button.png" alt=""></a>
        </div>
    <?php endforeach; ?>
</div>
</body>
<script>
        <?php if (count($widgets) > 3):?>
        $('.promo_slider').movingBoxes({
            startPanel: 3,      // start with this panel
            reducedSize: 1,       // non-current panel size: 80% of panel size
            fixedHeight: true,
            hashTags: false,
            width: 1000    // overall width of movingBoxes (not including navigation arrows)
            //panelWidth   : 0.3    // current panel width

        });
        <?php endif; ?>
</script>