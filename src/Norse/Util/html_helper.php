<?php
//require_once('sql_helper.php');
function arr2select($arrResult){
	$htmlOut = '';
	foreach($arrResult as $row){
		$htmlOut .= '<option value="'.$row['value'].'">'.$row['display'].'</option>';
	}
	return $htmlOut;
}


function arr2checkbox($arrResult, $strName){
	$htmlOut = '<div class="controls">';
	foreach($arrResult as $k=>$v){ 
		$htmlOut .= '<label class="checkbox" style="float:left;width:250px;"><input type="checkbox" name="'.$strName.'" type="checkbox" value="'.$k.'">'.$v.'</label>';
	}

	return $htmlOut.'</div>';
}

function int2Progress($intVal,$strClass){
	//used for the progress bars
	if($strClass=='heat'){
		$strClass='info';
		if($intVal>50){$strClass='warning';}
		if($intVal>74){$strClass='danger';}
	}
	return '<div class="progress progress-'.$strClass.'" style="margin:0;"><div class="bar" style="width:'.$intVal.'%">'.$intVal.'%</div></div>';
}

function arr2table($arrIn, $arrOptions = array('columns'=>'')){
	$intRecords = count($arrIn);
	if($intRecords > 0){
	if(isset($arrOptions['table']['id'])){$strID = $arrOptions['table']['id'];}else{$strID='dtable';}
	if(isset($arrOptions['table']['class'])){$strClass = $arrOptions['table']['class'];}else{
        //only use data tabkes when necessary, it's just clutter on small tables.
        if($intRecords > 10){
            $strClass='table table-striped table-bordered responsive dataTable';
        } else { $strClass='table table-striped table-bordered responsive'; }
     }
	$htmlOut = '<table class="'.$strClass.'" id="'.$strID.'" aria-describedby="dtable_info">';
	$htmlOut .= '<thead><tr role="row">';	
		//turn the keys into headers
		$frow = array_pop($arrIn);
		foreach (array_keys($frow) as $key){
			$htmlOut .= '<th class="sorting" role="columnheader" tabindex="0" aria-controls="dtable" rowspan="1" colspan="1" aria-sort="ascending"style="width: 226px;">' . $key . '</th>';
		}
		if(isset($arrOptions['actions'])&&$arrOptions['actions'] == true){$htmlOut .= '<th>Actions</th>';}
			array_push($arrIn, $frow);
			$htmlOut .= '</tr></thead>';
		//table body
			$htmlOut .= '<tbody role="alert" aria-live="polite" aria-relevant="all">';
		foreach ($arrIn as $v)
		{
			$x = 0; $strKey = '';
			//row
			 $htmlOut .= '<tr class="gradeA">';
			foreach ($v as $kk => $c)
			{
					if($x == 0){ $strKey = $c; }
					//cells
					if(isset($arrOptions['columns'][$kk])){
						foreach($arrOptions['columns'][$kk] as $xform=>$info){
							switch($xform){
							case 'link':
								$strTemp=str_replace('[val]', $c, $info);
								$c = '<a href="'.$strTemp.'">'.$c.'</a>';
								break;
							case 'flag':
								if(in_array($c,array('00','A1','A2','AP','EU')) === true){ $strImage='unknown';}else{$strImage=$c;}
								$c = '<a href="darklist_country.php?cc='.$c.'"><img src="'.$info.$strImage.'.png" />'.cc2Country($c);
								break;
							case 'class':
								$c = "<span class='$info'>$c</span>";
								break;
							case 'category':
								$c = cat2Category($c);
								break;
							case 'caticon':
								$c = '<i class="'.cat2icon($c).'"></i> '.$c;
								break;
							case 'progress':
								$c = int2Progress($c,'heat');
								break;
							}
						}
					}
				$htmlOut .= '<td>' . $c .'</td>';
				$x++;
			}
			if(isset($arrOptions['actions'])){
					if(isset($arrOptions['urlEdit'])){$htmlOut .= '<td class="editBox"><a href="'.$arrOptions['urlEdit'].'?id='.$strKey.'"><i class="icon-search" title="view"></i></a>';}
					if(isset($arrOptions['urlDelete'])){$htmlOut .= '<a href="'.$arrOptions['urlDelete'].'?id='.$strKey.'"> <i class="icon-ban-circle" title="delete"></i></a></td>';}
					if(isset($arrOptions['urlBrowseDarklist'])){
						$htmlOut .= '<td><a href="'.$arrOptions['urlBrowseDarklist'].'?cc='.$v['Country'].'&cat='.category2Cat($v['Category']).'"> <i class="icon-search" title="browse"></i></a></td>';
					}

				}
			$htmlOut .= '</tr>';
		}
		return $htmlOut . '</table>';
	}else return 'No Records Found';
	}