
<link rel="stylesheet" type="text/css" href="<?=HOST?>css/admin/admin.style.css?<?=REV?>">
<div class="wrapper clearfix">
	<div class="block">
		<div class="block-header">Список магазинов</div>
		<div class="block-content">
			<ul>
				<?php foreach($this->shopsList as $shop):?>
					<li><a href="<?=makeLink("/admin/shop/".$shop['id'])?>"><?=$shop['title']?></a></li>
					<?php endforeach;?>
			</ul>		
		</div>
	</div>
</div>