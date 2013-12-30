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

		<title>Untitled</title>



		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/redmond/jquery-ui-1.10.3.custom.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/chosen.min.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/toastr.min.css">
		<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/style.css">


		<script type="text/javascript" src="<?=HOST?>js/admin/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/jquery.carouFredSel-6.2.1-packed.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/chosen.jquery.min.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/toastr.min.js"></script>



		<script type="text/javascript" src="<?=HOST?>js/admin/tree.js"></script>

		<script type="text/javascript" src="<?=HOST?>js/admin/shop.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/widget.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/offer.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/main2.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/main.js"></script>
		<script type="text/javascript" src="<?=HOST?>js/admin/system.js"></script>
	</head>
	<body>
		<div class="wrapper clearfix">
			<div id="tabs">
				<ul class="tabList">


				</ul>



			</div>
		</div>
		<div class="hidden">
			<div class="categoryTpl">
				<div class="grid13">
					<h4>����� ���������</h4>
					<div class="treeHolder"></div>
				</div>
			</div>
			<div class="categoryOfferTpl">

				<div class="grid13">
					<h4>����� ���������</h4>
					<div class="treeHolder"></div>
				</div>
				<div class="grid13">
					<h4>����� ������</h4>
					<ol class="offerHolder">

					</ol>
				</div>
				<div class="grid13">
					<h4>������������ ������</h4>
					<div class="preview">
						<div class="previewPic">
							<img src="./images/preview.png" />
						</div>
						<div class="offerInfo">
						</div>
						<a href="#" class="btn addProduct">�������</a>
					</div>
				</div>
			</div>

			<div class="colorTpl clearfix">
				<h4>����� �����</h4>
			</div>




			<div class="block widget widgetTpl">
				<div class="block-header"></div>
				<div class="block-content">

				</div>
			</div>
			
			<div class="freePositionTpl">
				<div class="header"><h4><span class="positionNum"></span> �������</h4></div>
				<div class="body clearfix">
					<a href="#" class="btn choseProduct">������� �����</a>
					<a href="#" class="btn createRule">������� �������</a>
					<div class="categoryOfferHolder clearfix"></div>
					<div class="ruleHolder clearfix"></div>
					<div class="clearfix">
						<a href="#" class="btn savePosition">��������� �������</a>
						<span class="saved hidden">���������</span>
					</div>
				</div>
			</div>
			<div class="rule ruleTpl">
				<div class="block-content clearfix">
					<div class="categoryHolder clearfix"></div>
					
					<div class="colorHolder clearfix">
					</div>

				</div>

			</div>
			<div class="block choose-product chooseProduct chooseProductTpl clearfix hidden">
				<div class="block-header"><span>����� ������</span><div class="close">X</div></div>
				<div class="block-content clearfix">
					<div class="grid13">
						<h4>����� ���������</h4>
						<div class="treeHolder"></div>
					</div>
					<div class="grid13">
						<h4>����� ������</h4>
						<ol class="offerHolder">

						</ol>
					</div>
					<div class="grid13">
						<h4>������������ ������</h4>
						<div class="preview">
							<div class="previewPic">
								<img src="./images/preview.png" />
							</div>
							<div class="offerInfo">
							</div>
							<a href="#" class="btn addProduct">�������</a>
						</div>
					</div>
				</div>
				<div class="block-footer">

				</div>
			</div>
			<div class="treeTpl"></div>

			<div class="tabTpl">
				<div class="wrap">
					<div class="widgets">
						<a href="#" class="btn add-widget">+ ������</a>
						<div>
							<ul class="widget-list">

							</ul>
						</div>
					</div>

				</div>
			</div>
			<div class="positionTpl">
				<div class="header"><h4><span class="positionNum"></span> �������</h4></div>
				<div class="body">
					<a href="#" class="btn choseProduct">������� �����</a>
					<div class="categoryOfferHolder"></div>
				</div>
			</div>
			
			<div class="block smallWidget smallWidgetTpl widget">
				<div class="block-header">��������� ������</div>
				<div class="block-content">
					<a href="#" class="btn createRule">������� �������</a>
					<div class="ruleHolder"></div>
					<div class="positionHolder clearfix"  data-position-num="1"></div>
				</div>
			</div>
			<div class="block bigWidget bigWidgetTpl widget">
				<div class="block-header">������� ������</div>
				<div class="block-content">
					<a href="#" class="btn createRule">������� �������</a>
					<div class="ruleHolder"></div>
					<div class="positionHolder clearfix positionHolder1" data-position-num="1"></div>
					<div class="positionHolder clearfix positionHolder2" data-position-num="2"></div>
				</div>
			</div>
			<div class="block widget freeWidget freeWidgetTpl">
				<div class="block-header">��������� ������</div>
				<div class="block-content">
					
					<div class="positions">
						
					</div>
					<a href="#" class="btn addPosition">�������� �������</a>
					
				</div>
			</div>
			<div class="new-widget newWidgetTpl clearfix">
				<h3>�������� ������ �������</h3>

				<div class="clearfix">

					<div class="input-block">
						<span class="prepend">���: </span>
						<select class="widgetTypeList" data-placeholder="�������� ��������">

						</select> 
					</div>
					<div class="input-block">
						<span class="prepend">����: </span>
						<select class="widgetSkinList" data-placeholder="�������� ��������">

						</select>
					</div>
					<p>&nbsp;</p>
				</div>
				<a href="#" class="btn prepareWidget">������� ������</a>
				<div class="widgetHolder">
				</div>
				<div class="positions clearfix"></div>






				<div class="tut"></div>

				<div class="block clearfix preparedWidget">
					<div class="block-header">������</div>
					<div class="block-content clearfix">
						<div style="margin: 5px"><a class="btn generateWidgetPreview" href="# ">������������ �������</a></div>		
						<div style="margin: 5px;"><a href="#" class="btn saveWidget">��������� ������</a></div>
						<div class="widgetInfo"> 

							<div class="desc hidden">
								<div>������� �������: <span class="widgetCount">0</span></div>
								<div>���: <span class="widgetType"></span></div>
								<div>����: <span class="widgetSkin"></span></div>

								<div class="input-block">
									<span class="prepend">��� ��� �������:&nbsp;&nbsp;</span> 
									<div class="fl"><input type="text" class="custom fl widgetUrl" /></div>
									<div class="fl"><input type="button" value="�����������" onclick="clipBoard('.widgetUrl')" /></div>
								</div>
							</div>
						</div>
						<div class="widgetPreview">

						</div>
						
					</div>

				</div>

			</div>    


		</div>
		<div class="holder"></div>
	</body>
</html>

