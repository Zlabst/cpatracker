var timer_cols = false;

function update_cols(selected_option) {
	console.log(selected_option);
	switch (selected_option) {
		case 'sales':
			$('.col_l').hide();
			$('.col_s').show();
		break;
		case 'leads':
			$('.col_s').hide();
			$('.col_l').show();
		break;
		case 'currency_rub': 
			$('.sdata.usd').hide();
			$('.sdata.rub').show();
			
		break;
		case 'currency_usd': 
			$('.sdata.usd').show();
			$('.sdata.rub').hide();
			
		break;
	}
}
	
function update_stats(selected_option)
{
	switch (selected_option)
	{
		case 'clicks':
			$('#type_selected').val('clicks'); $('#rt_currency_section').addClass('invisible'); 
		break;

		case 'conversion':
			$('#type_selected').val('conversion'); $('#rt_currency_section').addClass('invisible');
		break;

		case 'lead_price':
			$('#type_selected').val('lead_price'); $('#rt_currency_section').removeClass('invisible');
		break;

		case 'roi':
			$('#type_selected').val('roi'); $('#rt_currency_section').addClass('invisible');
		break;

		case 'epc':
			$('#type_selected').val('epc'); $('#rt_currency_section').removeClass('invisible');
		break;

		case 'profit':
			$('#type_selected').val('sales'); $('#rt_currency_section').removeClass('invisible');
		break;

		case 'sales': 
			$('#sales_selected').val('1');
		break;

		case 'leads': 
			$('#sales_selected').val('0');
		break;

		case 'currency_rub': 
			$('#usd_selected').val('0');
		break;

		case 'currency_usd': 
			$('#usd_selected').val('1');
		break;

		default: break;
	}

	$('.sdata').hide();

	// Lead price was selected and we switched from leads to sales
	if ($('#sales_selected').val()==1)
	{
		if ($('#rt_leadprice_button').hasClass('active'))
		{
			$('#rt_leadprice_button').removeClass('active');
			$("#type_selected").val("clicks"); 
			$('#rt_clicks_button').addClass('active');			
			
			$("#rt_currency_section").hide();			
		}

		$('#rt_roi_button').show();
		$('#rt_epc_button').show();		
		$('#rt_profit_button').show();

		$('#rt_leadprice_button').hide();					
		if ($('#usd_selected').val()==1)
		{
			switch ($('#type_selected').val())
			{
				case 'clicks':
					$('.clicks').show();
					$('#rt_sale_section').show();
				break;
				case 'conversion':
					$('.conversion').show();
					$('#rt_sale_section').show();
				break;			
				case 'roi':
					$('.roi').show();
					$('#rt_sale_section').hide();
				break;
				default: 
					$('.'+$('#type_selected').val()).show();
					$('#rt_sale_section').hide();
					$('.rub').hide();
				break;
			}
		}
		else
		{
			switch ($('#type_selected').val())
			{
				case 'clicks':
					$('.clicks').show();
					$('#rt_sale_section').show();
				break;
				case 'conversion':
					$('.conversion').show();
					$('#rt_sale_section').show();
				break;		
				case 'roi':
					$('.roi').show();
					$('#rt_sale_section').hide();
				break;				
				default: 
					$('.'+$('#type_selected').val()).show();
					$('.usd').hide();
					$('#rt_sale_section').hide();
				break;
			}
		}	
	}
	else
	{
		$('#rt_roi_button').hide();
		$('#rt_epc_button').hide();		
		$('#rt_profit_button').hide();		
		$('#rt_leadprice_button').show();				
				
			switch ($('#type_selected').val())
			{
				case 'clicks':
					$('.leads_clicks').show();
				break;
				case 'conversion':
					$('.leads_conversion').show();
				break;
				case 'lead_price':
					if ($('#usd_selected').val()==1)
					{
						$('.leads_price.usd').show();
					}
					else
					{
						$('.leads_price.rub').show();
					}									
				break;				
				default: 
					$('.'+$('#type_selected').val()).show();
					$('.usd').hide();
				break;
			}
	}
}

function toggle_report_toolbar()
{
	if ($('#rt_type_section').hasClass('invisible'))
	{
		$('#rt_type_section').removeClass('invisible');
		$('#rt_sale_section').removeClass('invisible');
	}
	else
	{
		$('#rt_type_section').addClass('invisible');
		$('#rt_sale_section').addClass('invisible');
		$('#rt_currency_section').addClass('invisible');
	}
}