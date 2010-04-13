{* Smarty *}
{strip}

<!-- test/index -->

<div>
	<h3>Quiz</h3>
	<form action="/test/" method="post" name="quiz">

		<h4>Who is the first human in the space?</h4>
		<input type="text" name="quiz[q1]" value="{$quiz.q1}"/>
		{if $quizErrors.q1}
			<div id="q1error" class="error"><span>{$quizErrors.q1}</span></div>
		{/if}
		<br/><br/>

		<h4>How many keys are located on a standard keyboard?</h4>
		<select name="quiz[q2]">
			<option value="0"></option>
			<option value="1" {if $quiz.q2==1}selected="selected"{/if}>OMG, too many...</option>
			<option value="2" {if $quiz.q2==2}selected="selected"{/if}>105 for sure</option>
			<option value="3" {if $quiz.q2==3}selected="selected"{/if}>Get lost. I'm too lazy to calculate them.</option>
		</select>
		{if $quizErrors.q2}
			<div id="q2error" class="error"><span>{$quizErrors.q2}</span></div>
		{/if}
		<br/><br/>

		<h4>Where is The Great Wall of China?</h4>
		<input type="radio" id="quiz[q3][1]" name="quiz[q3]" value="1" {if $quiz.q3==1}checked="checked"{/if}/>
		<label for="quiz[q3][1]">Berlin</label>
		<br/>
		<input type="radio" id="quiz[q3][2]" name="quiz[q3]" value="2" {if $quiz.q3==2}checked="checked"{/if}/>
		<label for="quiz[q3][2]">Somewhere...</label>
		<br/>
		<input type="radio" id="quiz[q3][3]" name="quiz[q3]" value="3"  {if $quiz.q3==3}checked="checked"{/if}/>
		<label for="quiz[q3][3]">China</label>
		{if $quizErrors.q3}
			<div id="q3error" class="error"><span>{$quizErrors.q3}</span></div>
		{/if}
		<br/><br/>

		<h4>Which of these symbols are digits?</h4>
		<input type="checkbox" id="quiz[q4][1]" name="quiz[q4][]" value="1" {if !$quiz.q4|@empty && "1"|in_array:$quiz.q4}checked="checked"{/if}/>
		<label for="quiz[q4][1]">6</label>
		<br/>
		<input type="checkbox" id="quiz[q4][2]" name="quiz[q4][]" value="2" {if !$quiz.q4|@empty && "2"|in_array:$quiz.q4}checked="checked"{/if}/>
		<label for="quiz[q4][2]">%</label>
		<br/>
		<input type="checkbox" id="quiz[q4][3]" name="quiz[q4][]" value="3" {if !$quiz.q4|@empty && "3"|in_array:$quiz.q4}checked="checked"{/if}/>
		<label for="quiz[q4][3]">9</label>
		{if $quizErrors.q4}
			<div id="q4error" class="error"><span>{$quizErrors.q4}</span></div>
		{/if}
		<br/><br/>

		<h4>Could you please recognize text on the image and type it?</h4>
		{include file="Captcha/index"|cat:$smarty.const.TPLEXT}<br />
		<input type="text" name="quiz[captcha]" value="{$quiz.captcha}"/><br />
		{if $quizErrors.captcha}
			<div id="captchaerror" class="error"><span>{$quizErrors.captcha}</span></div>
		{/if}

		<input type="submit" value="I know I'm genius!"/>

		<input type="button" value="Can't touch this!" onclick="return test();"/>

	</form>
</div>

<div id="test"></div>

{literal}
<style type="text/css">
	.error span {color: red;}
</style>

<script type="text/javascript" language="JavaScript">

function test()
{
	var form = document.quiz;

	var handleSuccess = function(o){
		var div = document.getElementById('test');

		div.innerHTML = o.responseText;

		var result = YAHOO.lang.JSON.parse(document.getElementById('validationResult').firstChild.nodeValue);

		if(result === true)
		{
			window.location = '/test/congratulations/';
		}
		alert(result);
	};

	var handleFailure = function(o){

	};

	var callback =
	{
		success: handleSuccess,
		failure: handleFailure,
	};

	YAHOO.util.Connect.setForm('quiz');
	var request = YAHOO.util.Connect.asyncRequest('POST', '/test/index/true/', callback, 'qwer=asdf');

	return false;
}

</script>
{/literal}

<!-- /test/index -->

{/strip}