<?php
// @version $Id: default.php 10381 2008-06-01 03:35:53Z pasamio $
defined('_JEXEC') or die('Restricted access');
?>

<h4><?php echo $poll->title; ?></h4>
<form name="form2" method="post" action="index.php" class="poll">
	<fieldset>
		<?php for ($i = 0, $n = count($options); $i < $n; $i++) : ?>
		<input type="radio" name="voteid" id="voteid<?php echo $options[$i]->id; ?>" value="<?php echo $options[$i]->id; ?>" alt="<?php echo $options[$i]->id; ?>" />
		<label for="voteid<?php echo $options[$i]->id; ?>">
			<?php echo $options[$i]->text; ?>
		</label>
		<br />
		<?php endfor; ?>
	</fieldset>

	<?php // Captcha Extention patch rev. 4.5.0 Stable
	$dispatcher = &JDispatcher::getInstance();
	$results = $dispatcher->trigger( 'onCaptchaRequired', array( 'user.poll' ) );
	if ($results[0])
		$dispatcher->trigger( 'onCaptchaView', array( 'user.poll', 0, '<fieldset>', '</fieldset>' ) ); ?>
	<input type="submit" name="task_button" class="button" value="<?php echo JText::_('Vote'); ?>" />
	<a href="<?php echo JRoute::_('index.php?option=com_poll&id='.$poll->slug.$itemid.'#content'); ?>">
		<?php echo JText::_('Results'); ?></a>

	<input type="hidden" name="option" value="com_poll" />
	<input type="hidden" name="id" value="<?php echo $poll->id; ?>" />
	<input type="hidden" name="task" value="vote" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
