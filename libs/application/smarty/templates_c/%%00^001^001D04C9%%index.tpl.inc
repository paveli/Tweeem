<?php /* Smarty version 2.6.19, created on 2008-05-26 15:40:46
         compiled from Home/index.tpl */

$this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%00^001^001D04C9%%index.tpl.inc'] = '6f57cd90d792d0a966562b3d2d782d86';

?><?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'dynamic', 'Home/index.tpl', 12, false),array('modifier', 'date_format', 'Home/index.tpl', 36, false),)), $this); ?>
<?php
function _smarty_tplfunc_6f57cd90d792d0a966562b3d2d782d86_0(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if (isset ( $_smarty->_tpl_vars['login']['login'] )): ?><?php echo ''; ?><?php echo $_smarty->_tpl_vars['login']['login']; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}


function _smarty_tplfunc_6f57cd90d792d0a966562b3d2d782d86_1(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if (isset ( $_smarty->_tpl_vars['loginErrors']['login'] )): ?><?php echo '<span class="error">'; ?><?php echo $_smarty->_tpl_vars['loginErrors']['login']; ?><?php echo '</span>'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}


function _smarty_tplfunc_6f57cd90d792d0a966562b3d2d782d86_2(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if (isset ( $_smarty->_tpl_vars['loginErrors']['password'] )): ?><?php echo '<span class="error">'; ?><?php echo $_smarty->_tpl_vars['loginErrors']['password']; ?><?php echo '</span>'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}


function _smarty_tplfunc_6f57cd90d792d0a966562b3d2d782d86_3(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if (isset ( $_smarty->_tpl_vars['login']['remember'] ) && $_smarty->_tpl_vars['login']['remember']): ?><?php echo ' checked="checked"'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}


function _smarty_tplfunc_6f57cd90d792d0a966562b3d2d782d86_4(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if (isset ( $_smarty->_tpl_vars['loginErrors']['invalid'] )): ?><?php echo '<span class="error">'; ?><?php echo $_smarty->_tpl_vars['loginErrors']['invalid']; ?><?php echo '</span>'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}


function _smarty_tplfunc_6f57cd90d792d0a966562b3d2d782d86_5(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php echo ((is_array($_tmp=time())) ? $_smarty->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d %H:%M:%S") : smarty_modifier_date_format($_tmp, "%Y-%m-%d %H:%M:%S")); ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}



?>
