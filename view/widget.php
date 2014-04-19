<!DOCTYPE html>
<html>
<head>

	<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Lang" content="en">

	<meta name="author" content="">
	<meta name="description" content="">
	<meta name="keywords" content="">

	<title>Виджет</title>



	<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/redmond/jquery-ui-1.10.3.custom.css">
	<link rel="stylesheet" type="text/css" href="<?=HOST?>css/widget/style.css?<?=REV?>">

	<script type="text/javascript" src="<?=HOST?>js/admin/jquery-1.9.1.js"></script>
	<script type="text/javascript" src="<?=HOST?>js/admin/jquery.carouFredSel-6.2.1-packed.js"></script>

</head>
<body>
	<div class="slider">
		<div class="arrow-left"></div>
		<div class="arrow-right"></div>
		<ul>

			<?php  //$widgets = $widget->getWidget($widgetsId);
				foreach ($this->widgets as $key => $widget):
                    $picture = @unserialize($widget['picture']);
                    ?>
				
				<li class="w <?php if (count($this->widgets) <= 3) echo 'widget';?> widget_<?= $widget['id'] ?>">
					<div class="pic">
						<a href="<?= $widget['url']; ?>" target="_blank" class="refer_link"><img class="offer_img"
								src="<?=($picture !== false) ? $picture[0] : $widget['picture'] ?>"
								alt="" ></a>
						<div class="desc">
							<?= mb_substr($widget['title'], 0, 100) ?>
						</div>
					</div>
					<div>
						<span class="price_text">
							<span class="int_val"><?= $widget['price']['viewPrice']['intValue'] ?> руб.</span>
						</span>

					</div>
					<div class="btn"><a class="refer_link button_sell" href="<?= $widget['url']; ?>" target="_blank"><img src="<?=HOST?>images/sell_button.png"></a></div>
				</li>
				<?php endforeach; ?>
		</ul>
	</div>

	<div class="copyright">
       <p>
           <img class="logo" src="<?= HOST ?>images/logo.png" alt="">
           <span class="copyright-text">OOO «Приват Трэйд»,111033, г.Москва, ул. Самокатная, д.1, стр.21; ОГРН 1087746760397</span>
       </p>
    </div>
</body>
<script>

	$(function(){
        $('.refer_link').click(function(){
            $.ajax({
                type: 'POST',
                url: '<?=HOST?>/handler',
                data: {methodName: 'referrerAdd', params: {'widgetId': <?=$this->widgetId?>}},
			dataType: "json"
            });
        })
			var $arrowLeft = $(".slider .arrow-left"),
			$arrowRight = $(".slider .arrow-right");

			var $carousel = $(".slider ul").carouFredSel({
					circular: false,
					infinite: false,
					align: "left",
					width: "100%",
					auto: false,
                    height: 349,
					scroll : {
						items: 1, 
						onBefore: function(){
						},
						onEnd: function(direction){
						}
					},
					prev	: {
						button	: ".arrow-left",
						key		: "left",
						onEnd: function(){
							$arrowLeft.hide();
						},
						onAfter: function(){
							$arrowRight.show();
						}
					},
					next	: {
						button	: ".arrow-right",
						key		: "right",
						onAfter: function(){
							$arrowLeft.show();
						},
						onEnd: function(){
							$arrowRight.hide();
						}
					}
			});
        $('.arrow-left').hover(
            function(){ $(this).addClass('arrow-left-inactive') },
            function(){ $(this).removeClass('arrow-left-inactive') }
        );
        $('.arrow-right').hover(
            function(){ $(this).addClass('arrow-right-inactive') },
            function(){ $(this).removeClass('arrow-right-inactive') }
        );
        $('.caroufredsel_wrapper').css('margin-left', '28px');
        setArrowPosition();

        $(window).resize(function () {
            setArrowPosition();
        });

	});
    function setArrowPosition(){
        var firstElementLeft = $('li.w').first().offset().left,
            elementWidth = $('li.w').first().outerWidth(true),
            element = $('li.w').first(),
            widgetViewCount = 0;
        $('.arrow-left').css('left', firstElementLeft - $('.arrow-left').width() - 2);
        if ($(document).width() < $('li.w').last().offset().left){
            widgetViewCount = Math.floor(($(document).width() - firstElementLeft) / (elementWidth));
        }
        var widgetViewOffset = widgetViewCount ? $('li.w').eq(widgetViewCount - 1).offset().left : firstElementLeft;
        var lastElementRight = $(document).width() - widgetViewOffset - element.width();
        $('.arrow-right').css('right', lastElementRight - $('.arrow-right').width() - 2);
        $('.copyright').css('margin-left', firstElementLeft + 'px');
    }
</script>