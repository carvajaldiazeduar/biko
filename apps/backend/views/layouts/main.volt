
<div class="navbar navbar-default">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<li>{{ link_to('index', 'BIKØ', "class": "navbar-brand") }}</li>
	</div>
	<div class="navbar-collapse collapse navbar-responsive-collapse">
		<ul class="nav navbar-nav">
			{% set options = [
					'category/software'        : ['Software', 'icon-home'],
					'category/hardware'        : ['Hardware', 'icon-home'],
					'category/network'         : ['Network', 'icon-wrench']
				] %}
			{% set controller = dispatcher.getControllerName() %}
			{% for option, label in options %}
				{% if controller == option %}
					<li class="active">{{ link_to(option, '<span class="' ~ label[1] ~ ' icon-black"></span> ' ~ label[0]) }}</li>
				{% else %}
					<li>{{ link_to(option, '<span class="' ~ label[1] ~ '"></span> ' ~ label[0]) }}</li>
				{% endif %}
			{% endfor %}
		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li>{{ link_to('help', 'Help') }}</li>
		</ul>
	</div>
</div>

<div class="main-container container">
	{{ content() }}
</div>

<footer>
	<div align="center">
		<a target="_blank" href="http://phalconphp.com/">Phalcon Framework</a> - 2011 - {{ date('Y') }}
	</div>
</footer>
