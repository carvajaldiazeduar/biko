<?php

namespace Biko\HyperForm;

use Phalcon\Mvc\User\Component;

class Builder extends Component
{
	private $form;

	private $config;

	private $page;

	/**
	 * @param Phalcon\Forms\Form $form
	 * @param array $config
	 */
	public function __construct($form, $config, $page)
	{
		$this->form   = $form;
		$this->config = $config;
		$this->page   = $page;
	}

	/**
	 * This view allows a user to filter records according to enabled fields
	 */
	public function searchForm()
	{
		echo $this->view->getContent();

		echo '<div class="toolbar">';

		/** Create button */
		echo '<div class="pull-right">';
		echo $this->tag->linkTo(array(
			array('for' => 'hyperform-create'),
			"<span class='glyphicon glyphicon-plus'></span> Add " . $this->config['singular'],
			"class" => "btn btn-primary btn-sm"
		));
		echo '</div>';

		/** Import button */
		if (!isset($this->config['disableImport'])) {
			echo '<div align="right" class="pull-right">';
			echo $this->tag->linkTo(array(
				$this->config['controller'] . "/import",
				"<span class='glyphicon glyphicon-import'></span> Import",
				"class" => "btn btn-primary btn-sm"
			));
			echo '</div>';
		}

		echo '</div>';

		echo '<div class="well">';

		echo '<fieldset><legend>Search ' . $this->config['plural'] . '</legend>';

		echo '<form method="post" action="', $this->url->get(array('for' => 'hyperform-search')), '" autocomplete="off">';

		echo '<div class="row">';
		foreach ($this->form->getElements() as $element) {

			/** Only elements with attribute 'searcheable' are shown */
			if ($element->getUserOption('searcheable')) {
				echo '<div class="form-group col-md-12">
					<label class="control-label" for="',  $element->getName(), '">' . $element->getLabel() . '</label>
					', $element->render(array('class' => 'form-control'));
				echo '</div>';
			}

		}
		echo '</div>';

		echo '<div class="row">
			<div class="form-group col-md-12">
				', $this->tag->submitButton(array("Search", "class" => "btn btn-primary")),
			'</div>
		</div>';

		/*echo '<div align="right">
			<div class="report-type well">
				Output: ' . $this->tag->select(array("reportType", array(
					"P" => "Screen",
					"H" => "HTML",
					"E" => "Excel",
					"D" => "PDF"
				))) . '
			</div>
		</div>';*/

		echo '</form></fieldset>';

		echo '</div>';
	}

	public function browseForm()
	{
		/* Show messages generated by the flash component */
		echo $this->view->getContent();

		echo '<div class="row">';

			/* "Back" button */
			echo '<div class="col-md-6">';
				echo $this->tag->linkTo(array(
					$this->config['controller'],
					"&larr; Go Back",
					"class" => "btn btn-sm btn-default"
				));
			echo '</div>';

			/* Create button */
			echo '<div class="col-md-6" align="right">';
				echo $this->tag->linkTo(array(
					array('for' => 'hyperform-create'),
					"<i class='glyphicon glyphicon-plus'></i> Add " . $this->config['singular'],
					"class" => "btn btn-primary btn-sm"
				));
			echo '</div>';

		echo '</div>';

		echo '<table class="table table-bordered table-striped" align="center">
		<thead>
			<tr>';
			$number = 0;
			foreach ($this->form->getElements() as $element) {
				if ($element->getUserOption('browseable')) {
					echo '<th>' . $element->getLabel() . '</th>';
					$number++;
				}
			}
			echo '</tr>
		</thead>
		<tbody>';

		foreach ($this->page->items as $record) {

			echo '<tr class="browse-row">';
			foreach ($this->form->getElements() as $element) {

				/* Only elements market as browseable are used */
				if ($element->getUserOption('browseable')) {

					/* Check if there is a method called "get{ElementName}Detail" */
					$method = 'get' . $element->getName() . 'Detail';
					if (method_exists($record, $method)) {
						echo '<td>' . $record->$method() . '</td>';
					} else {

						/* Check if we have a relation */
						$relation = $element->getUserOption('relation');
						if ($relation) {
							$relationRecord = $record->$relation;
							if ($relationRecord) {
								echo '<td>' . $relationRecord->name . '</td>';
							} else {
								echo '<td>?</td>';
							}
						} else {

							/* Use the value as usual */
							$value = $record->readAttribute($element->getName());
							echo '<td>' . $value . '</td>';
						}
					}
				}

			}

			$primaryKey = $record->readAttribute($this->config['primaryKey']);

			/* Edit button */
			echo '<td width="5%">', $this->tag->linkTo(array(
				array('for' => 'hyperform-edit', 'primary-key' => $primaryKey),
				'<span class="glyphicon glyphicon-pencil"></span>',
				"class" => "btn btn-sm btn-default",
				"title" => "Edit"
			)) . '</td>';

			/* Delete button */
			echo '<td width="5%">
				<a onclick="removeRecord(' . $primaryKey . '); return false;" href="#" class="btn btn-sm btn-default" "title="Delete">
					<span class="glyphicon glyphicon-remove"></span>
				</a>
			</td>';

			/* Revisions button */
			echo '<td width="5%">', $this->tag->linkTo(array(
				$this->config['controller'] . "/rcs/" . $primaryKey,
				'<span class="glyphicon glyphicon-book"></span>',
				"class" => "btn btn-sm btn-default",
				"title" => "Revisions"
			)) . '</td>';
			echo '</tr>';

		}
		echo '</tbody>';

		/*echo '<tbody>
			<tr>
				<td colspan="', ($number + 3), '" align="left">
					<table width="100%" class="paginator">
						<tr>
							<td align="left" width="50%">
								<div class="btn-group">
									', $this->tag->linkTo(array($this->config['controller'] . "/search",                       '<i class="icon-fast-backward"></i>', "class" => "btn")) . '
									', $this->tag->linkTo(array($this->config['controller'] . "/search?page=" . $page->before, '<i class="icon-step-backward"></i>', "class" => "btn")) . '
									', $this->tag->linkTo(array($this->config['controller'] . "/search?page=" . $page->next,   '<i class="icon-step-forward"></i>', "class" => "btn")) . '
									', $this->tag->linkTo(array($this->config['controller'] . "/search?page=" . $page->last,   '<i class="icon-fast-forward"></i>', "class" => "btn")) . '
								</div>
							</td>
							<td align="right" width="50%">
								<span class="help-inline">', $page->current, '/', $page->total_pages, ' Total: ', $page->total_items, '</span>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<tbody>*/

		echo '</table>';

		/*echo '<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-header alert-warning">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">Eliminar</h3>
			</div>
			<div class="modal-body"><p>¿Seguro desea eliminar permanentemente el registro?</p></div>
			<div class="modal-footer">
				<a class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</a>
				<a class="btn btn-primary alert-warning" onclick="window.location = \'' . $url->get($config['controller'] . "/delete/") . '\' + window.tempId">Eliminar</a>
			</div>
		</div>

		<script type="text/javascript">function removeRecord(id) { window.tempId = id; $("#myModal").modal("show"); }</script>';*/

	}

	public function createForm($di, $form, $config)
	{

		echo '<form method="post" autocomplete="off" enctype="multipart/form-data">';
		echo $this->view->getContent();

		echo '<div align="left">';
			 echo '<table width="100%">';
					echo '<tr>';
						echo '<td valign="top">';

						echo '<ul class="pager toolbar">';
						echo '<li class="previous pull-left">';
						echo $di['tag']->linkTo(array($config['controller'] . '/search', "&larr; Atrás"));
						echo '</li>';
						echo '<li class="previous pull-right">';
						echo $di['tag']->submitButton(array("Crear", "class" => "btn btn-success"));
						echo '</li>';
						echo '</ul>';

						echo '<h4>Crear ', self::$_config['singular'], '</h4>';
						$form->show();
						echo '</td>';
						/*echo '<td width="20%" valign="top" align="center">';
							echo $di['tag']->submitButton(array("Crear", "class" => "btn btn-success"));
							echo '<div align="center" class="cancel">';
							echo $di['tag']->linkTo(array(self::$_config['controller'], "Cancelar", "class" => "btn btn-small"));
							echo '</div>';
						echo '</td>';*/
					echo '</tr>';
			echo '</table>';
		echo '</div>';
		echo '</form>';
	}

	public function editForm()
	{

		echo '<form method="post" autocomplete="off" enctype="multipart/form-data">';
		echo $this->view->getContent();

		echo '<ul class="pager toolbar">';
			echo '<li class="previous pull-left">';
				echo $this->tag->linkTo(array($this->config['controller'] . '/search', "&larr; Go Back"));
			echo '</li>';
			echo '<li class="pull-right">';
				echo $this->tag->submitButton(array("Actualizar", "class" => "btn btn-sm btn-success"));
			echo '</li>';
		echo '</ul>';


		echo '<div class="well">';

		echo '<fieldset><legend>Edit ' . $this->config['singular'] . '</legend>';

		$this->form->show();

		echo '</fieldset></form>';
	}

	public static function importForm($di, $form, $config)
	{

		echo '<form method="post" autocomplete="off" enctype="multipart/form-data">';
		echo $di['view']->getContent();

		echo '<div align="left">';
			 echo '<table width="100%">';
					echo '<tr>';
						echo '<td valign="top">';
						echo '<h4>Importar ', self::$_config['plural'], '</h4>';
						echo '<div class="import well">', $di['tag']->fileField('archivo'), '</div>';
						echo '</td>';
						echo '<td width="20%" valign="top" align="center">';
							echo $di['tag']->submitButton(array("Importar", "class" => "btn btn-success"));
							echo '<div align="center" class="cancel">';
							echo $di['tag']->linkTo(array(self::$_config['controller'], "Cancelar", "class" => "btn btn-small"));
							echo '</div>';
						echo '</td>';
					echo '</tr>';
			echo '</table>';
		echo '</div>';
		echo '</form>';

		echo '<div align="left">';
		echo '<p>Por favor importe un archivo de Microsoft Excel de máximo 4000 registros con las siguientes columnas:</p>
		<table class="table table-bordered table-striped table-condensed" align="center"><tr><th>Nombre</th><th>Tipo</th><th>Requerido</th><th>Descripción</th>';
		foreach ($form->getElements() as $element) {

			$required = false;
			$validators = $element->getValidators();
			if (count($validators)) {
				foreach ($validators as $validator) {
					if ($validator instanceof PresenceOf) {
						$required = true;
						break;
					}
				}
			}

			if ($element instanceof Select) {

				$domain = array();
				foreach ($element->getOptions() as $key => $value) {
					if (is_object($value)) {
						$domain[] = $value->name;
					} else {
						if ($key) {
							$domain[] = $value;
						}
					}
				}

				echo '<tr>
					<td>', $element->getLabel(), '</td>
					<td>Lista</td>
					<td>', $required ? 'SI' : 'NO', '</td>
					<td>Posibles Valores:' . join(', ', $domain) . '</td>
				</tr>';
			} else {

				$maxlength = $element->getAttribute('maxlength');
				if (!$maxlength) {
					$maxlength = '200';
				}

				echo '<tr>
					<td>', $element->getLabel(), '</td>
					<td>Texto</td>
					<td>', $required ? 'SI' : 'NO', '</td>
					<td>Tamaño Máximo: ' . $maxlength . '</td>
				</tr>';
			}
		}
		echo '</table></div>';

		echo '<div align="center" class="well" style="padding-bottom:10px">';
		echo '<p>Plantilla base para importación en Excel ' . $di['tag']->linkTo(array(self::$_config['controller'] . '/download', "Descargar", "class" => "btn btn-primary btn-small")) . '</p>';
		echo '</div>';
	}

	public static function rcsForm($di, $record, $revisions, $form, $config)
	{

		$metaData = $record->getModelsMetaData();
		$columnMap = $metaData->getColumnMap($record);

		echo $di['view']->getContent();

		echo '<ul class="pager toolbar">';
		echo '<li class="previous pull-left">';
		echo $di['tag']->linkTo(array($config['controller'], "&larr; Atrás"));
		echo '</li>';
		echo '</ul>';

		$escaper = $di['escaper'];
		foreach ($revisions as $row) {

			$revision = Revisions::findFirst($row->id);

			echo '<table width="100%">';
			echo '<tr><td valign="top" width="40%">';
			echo '<table cellpadding="5">';
			echo '<tr><td align="right">Usuario</td><td><strong>', $revision->user->name, '</strong></td></tr>';
			echo '<tr><td align="right">Fecha</td><td><strong>', date('d/m/Y H:i', $revision->createdAt), '</strong></td></tr>';
			echo '</table>';
			echo '</td><td width="60%" valign="top">';
			echo '<table cellpadding="5" class="rcs table table-bordered table-striped" width="100%">';
			foreach ($revision->records as $record) {

				if (isset($columnMap[$record->fieldName])) {
					$attributeName = $columnMap[$record->fieldName];
				} else {
					$attributeName = $record->fieldName;
				}

				if ($form->has($attributeName)) {
					$element = $form->get($attributeName);

					$value = $record->value;
					if (mb_strlen($value) > 20) {
						$textValue = mb_substr($value, 0, 20) . '...';
					} else {
						$textValue = $value;
					}

					if ($record->changed == 'Y') {
						echo '<tr class="success"><td align="right" width="40%">', $element->getLabel(), '</td><td width="60%" title="', $value, '">', $escaper->escapeHtml($textValue), '</td></tr>';
					} else {
						echo '<tr><td align="right" width="40%">', $element->getLabel(), '</td><td width="60%" title="', $value, '">', $escaper->escapeHtml($textValue), '</td></tr>';
					}
				}

			}
			echo '</table>';
			echo '</td></tr><tr><br><tr>';
			echo '</table>';
		}

	}

}