<?php if (!$include_flag){exit();} ?>
<div class="row">
    <div class="col-md-12">
        <h3>Целевые страницы</h3>
    </div>
</div>
	
<div class="row" id="master-form">
    <div class="col-md-12">
    	<p>Для корректного учета посетителей на ваши целевые страницы установите, пожалуйста, код счетчика:
    	<pre>&lt;script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/track/cookie.php"&gt;&lt;/script&gt;</pre>
    	Устанавливать код счётчика необходимо перед тегом &lt;/body&gt; в HTML-код страницы.</p><br />
    	<p>Если Вы хотите вести учёт регистраций и продаж на собственном сайте, разместите код счетчика на странице, с которой осуществляется продажа и обеспечте выполнение нижеследующего кода при осуществлении продажи или регистрации.
    	<pre>&lt;script&gt;cpatracker_add_lead(profit);&lt;/script&gt;</pre> где profit - сумма продажи в валюте RUB (российский рубль), или 0 если нужно учесть только регистрацию. </p>
    </div>
</div>