<?php /* Smarty version 2.6.19, created on 2008-06-10 14:46:38
         compiled from Ajax/list.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'Ajax/list.tpl', 22, false),array('block', 'dynamic', 'Ajax/list.tpl', 23, false),)), $this); ?>
<?php $this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%E4^E42^E4268285%%list.tpl.inc'] = 'becb783ef1d83601810de668fda57340'; ?><?php echo '<!-- home/workspace/list -->'; ?><?php if (isset ( $this->_tpl_vars['list'] )): ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['list']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['item']):
        $this->_foreach['list']['iteration']++;
?><?php echo '<div class="item'; ?><?php if (($this->_foreach['list']['iteration'] <= 1)): ?><?php echo ' first'; ?><?php elseif (($this->_foreach['list']['iteration'] == $this->_foreach['list']['total'])): ?><?php echo ' last'; ?><?php endif; ?><?php echo '"><img src="'; ?><?php echo $this->_tpl_vars['item']['profile_image_url']; ?><?php echo '" alt="'; ?><?php echo $this->_tpl_vars['item']['name']; ?><?php echo '" align="top"/><div class="text"><span class="name">'; ?><?php echo $this->_tpl_vars['item']['name']; ?><?php echo '</span><span class="screenName">'; ?><?php echo $this->_tpl_vars['item']['screen_name']; ?><?php echo '</span></div></div>'; ?><?php if (! ($this->_foreach['list']['iteration'] == $this->_foreach['list']['total'])): ?><?php echo '<div class="line"></div>'; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?><?php endif; ?><?php echo '<!-- Cache time: '; ?><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d %H:%M:%S") : smarty_modifier_date_format($_tmp, "%Y-%m-%d %H:%M:%S")); ?><?php echo ' --><!-- Dynamic time: '; ?><?php if ($this->caching && !$this->_cache_including): echo '{nocache:becb783ef1d83601810de668fda57340#0}'; endif;$this->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d %H:%M:%S") : smarty_modifier_date_format($_tmp, "%Y-%m-%d %H:%M:%S")); ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); if ($this->caching && !$this->_cache_including): echo '{/nocache:becb783ef1d83601810de668fda57340#0}'; endif;?><?php echo ' --><!-- /home/workspace/list -->'; ?>