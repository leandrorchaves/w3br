<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     tinyMCE
 * Version:  0.8
 * Date:     07 Mar, 2007
 * Author:	 Rafael Dohms <rafael@rafaeldohms.com.br>
 * Purpose:  inserts TinyMCE Code and inicializes editor
 * Input:    mode = modo de sele��o dos campos
 *           elements = textarea alvo para editor
 * 
 * -------------------------------------------------------------
 */
function smarty_function_tinyMCE($params, &$smarty)
{	

	//Valores padrao
	$atribs['mode'] = 'specific_textareas';
	$atribs['theme_advanced_toolbar_location'] = 'top';
	$atribs['theme_advanced_toolbar_align'] = 'left';
	$atribs['theme_advanced_blockformats'] = "address,pre,h1,h2,h3,h4,h5,h6";
	$atribs['theme_advanced_resizing'] = "true";
	$atribs['theme_advanced_buttons1'] = "bold,italic,underline,separator,justifyleft,justifycenter,justifyright,separator,bullist,numlist,separator,outdent,indent,separator,link,unlink,image,separator,sup,sub,separator,charmap";
	$atribs['theme_advanced_buttons2'] = "undo,redo,code";
	$atribs['theme_advanced_buttons3'] = "";
	$atribs['force_br_newlines'] = "true";
	$atribs['plugins'] = "advimage";
	$atribs['dialog_type'] = "modal";
	$atribs['language'] = "pt_br";
	$atribs['convert_newlines_to_brs'] = "true";
	
	//Iterar pelos atributos passados
	foreach($params as $_key => $_val) {	
		if (substr($_key,0,1) == '_'){
			$key = substr($_key,1);
			$$key = $_val;
		}else{
			$atribs[$_key] = $_val;
		}
		
	}
	
	//Definir path do arquivo do tiny
	$src = ($altsrc != '')? $altsrc:"";
	
	//Verificar se devemos ou n�o inserir biblioteca
	if (!$GLOBALS['Smarty']['tinyMCE']['JS']){
		$code  	   = '<script language="javascript" type="text/javascript" src="'.$src.'"></script>';
	}
	
	//Iniciar c�digo de inicializa��o do editor
	$code 	  .= '<script language="javascript" type="text/javascript">';
	$code 	  .= 'tinyMCE.init({';
	//Iterar por atributos
	foreach($atribs as $atr=>$value){
		$code .= $atr.': "'.$value.'",';
	}
	//Finalizar
	$code .= 'blank: "none"'; //Evitar problem de virgula perdida
	$code .= '});';
	$code .= '</script>';
	
	//Setar variavel global de biblioteca inserida
	$GLOBALS['Smarty']['tinyMCE']['JS'] = true;
	
	//Retornar código
    return $code;
}

/* vim: set expandtab: */

?>