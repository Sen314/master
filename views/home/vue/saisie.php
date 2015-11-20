<?php
/*
 * Saisie des données pour une activté et un site
 */
//Declaration de l'activite
$activity = new App_Model_Activity();
$activity= $activity->load($_GET["act"]);
$site=new App_Model_Site;
$site= $site->load( $_GET['sit']);
$items=$activity->getItemWhithoutForm();
   

if (isset($_POST['valeur'])) //Si il y'a des valeurs renseignées on les traite
{
    extract($_POST);
    foreach ($items as $item)
    {
        if(is_null($item->formula))
        {
            $value=$valeur[$item->tag];
            if($value!=null)
            {
               App_Model_Value::setValueByItem($site->id,$item->id,$date,$value);
            }
        }
    }
}
?>  

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
              <h3 class="box-title"><?php echo $activity->name; ?></h3>
            </div>
            <div class="box-body table-responsive">
                <form role="form" method="post" >
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right" name="dateChoisie" id="dateChoisie" value="<?php if(isset($_POST['dateChoisie']) and !is_null($_POST['dateChoisie'])) { echo $_POST['dateChoisie']; } else { echo date('d/m/Y');} ?>"/>
                            </div><!-- /.input group -->
                        </div><!-- /.form group -->
                            <div class="box-footer">
                            <input type="submit" value="Charger" />
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.box -->
    </div>
</div>

<?php
if (isset($_POST['dateChoisie']) and $_POST['dateChoisie']!="") 
{
    $date=$_POST['dateChoisie'];
    
 //Date de la derniere mise à jour des valeurs, pour outes les valeurs allant du 1er jour du mois M-1 à la journée d'aujoud'hui
 $lastUpdate=  App_Model_Value::getLastUpdateByActivitySite($activity->id,$site->id,$date,$date);
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header">
				<h3 class="box-title">Indicateurs</h3>
			</div><!-- /.box-header -->
			<form role="form" method="post" >
				<div class="form-group">
                    <input type="hidden" class="form-control" type="text" name="date" value="<?php echo $date; ?>"/>
				</div>
				<div class="box-body table-responsive">
					<table id="tabItem" class="table table-bordered table-hover">
						<thead>
						</thead>
						<tbody>
						<?php
						foreach ($items as $item)	
						{ ?>
							<tr>
								<td><?php echo $item->name;?></td>				
								<?php                                
                                    $values=App_Model_Value::getValueByIdItem($site->id,$item->id,$date, $date);
                                    if(!empty($values)) //Il y'a une valeur à la date indique
                                    {
                                        foreach ($values as $value)
                                        {
                                            echo '<td><input type="text" name=valeur['.$item->tag.'] size="5" pattern="[-+]?[0-9]+(\.[0-9]+)?" value="'.$value->value.'"</td>';
                                        }
                                    }
                                    else
                                    {
                                        echo '<td><input type="text" name=valeur['.$item->tag.'] size="5" pattern="[-+]?[0-9]+(\.[0-9]+)?"</td>';
                                    }
								?>
							</tr>
						<?php
						}
						?>
						<tfoot>
                        </tfoot>
					</table>
				</div><!-- /.box-body -->
				<div class="box-footer">
					<input type="submit" value="Envoyer" />
				</div>
			</form>
            <div>
              <i> Dernière mise à jour: <?php echo $lastUpdate; ?></i>
            </div>
		</div><!-- /.box -->
	</div>
</div>
<?php
}
?>
<!-- Js pur le daeicker -->
<script type="text/javascript">
    $(function () {

        //Date range picker
        $('#dateChoisie').datepicker({
            language: 'fr'
        });
      });
</script>