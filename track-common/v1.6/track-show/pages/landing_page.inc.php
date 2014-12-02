<?php if (!$include_flag){exit();} ?>
<div class="row">
    <div class="col-md-12">
        <h3>Целевые страницы</h3>
    </div>
</div>
	
<div class="row" id="master-form">
    <div class="col-md-12">
    	<p>Для корректного учета посетителей на ваши целевые страницы установите, пожалуйста, код счетчика:
    	<pre>&lt;!--cpatracker.ru start--&gt;&lt;script type="text/javascript"&gt; ;(function(){if(window.cpa_inited)return;window.cpa_inited=true;var a=document.createElement("script");a.type="text/javascript";var b=""; if(typeof this.href!="undefined"){b=this.href.toString().toLowerCase()}else{b=document.location.toString().toLowerCase()}; a.async=true;a.src="//<?php echo $_SERVER['HTTP_HOST']; ?>/track/cookie.js?rnd="+Math.random(); var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(a,s)})();&lt;/script&gt;&lt;!--cpatracker.ru end--&gt;</pre>
    	Устанавливать код счётчика необходимо перед тегом &lt;/body&gt; в HTML-код страницы.</p><br />
    	<p>Если Вы хотите вести учёт регистраций и продаж на собственном сайте, разместите код счетчика на странице, с которой осуществляется продажа и обеспечте выполнение нижеследующего кода при осуществлении продажи или регистрации.
    	<pre>&lt;script&gt;cpatracker_add_lead(profit);&lt;/script&gt;</pre> где profit - сумма продажи в валюте RUB (российский рубль), или 0 если нужно учесть только регистрацию. </p>
    </div>
</div>