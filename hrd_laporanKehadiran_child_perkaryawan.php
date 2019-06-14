<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');

function tanggalDb($tgl){
	$t=explode("-",$tgl);
	$tgl=$t[2].'-'.$t['1'].'-'.$t['0'];
	return $tgl;
}

function tglIndo($tgl){
	$t=explode('-',$tgl);
	return $t[2].'/'.$t[1].'/'.$t[0];
}

function getHari($tanggal){
	if($tanggal!=''){
	$day = date('D', strtotime($tanggal));
	$hari= array(
		'Sun' => 'Minggu',
		'Mon' => 'Senin',
		'Tue' => 'Selasa',
		'Wed' => 'Rabu',
		'Thu' => 'Kamis',
		'Fri' => 'Jumat',
		'Sat' => 'Sabtu'
	);
	return $hari[$day];
	}
}

function getTimeDiff($time1,$time2){
	$date1 = strtotime($time1);
	$date2 = strtotime($time2);
	$interval = $date2 - $date1;
	$seconds = $interval % 60;
	$minutes = floor(($interval % 3600) / 60);
	$hours = floor($interval / 3600);
	if($hours<0){
		return "-";
	}
	return sprintf('%02d', $hours).":".sprintf('%02d', $minutes).":".sprintf('%02d', $seconds);
}


$jamMasuk = [
		'MDHO'=>['reguler'=>'08:00:00',
				 'shiftPagi'=>'08:00:00',
				 'shiftMalam'=>'20:00:00'],
		'BGRE'=>['reguler'=>'07:00:00',
				 'shiftPagi'=>'08:00:00',
				 'shiftMalam'=>'20:00:00'],

	]; 

	$jamMasuk['SLRO']=$jamMasuk['MDHO'];
	$jamMasuk['KBHE']=$jamMasuk['KAME']=$jamMasuk['DRME']=$jamMasuk['BGRE'];

	$jamPulang = [
		'MDHO'=>['reguler'=>'17:00:00',
				 'shiftPagi'=>'19:59:00',
				 'shiftMalam'=>'07:59:00'],
		'BGRE'=>['reguler'=>'16:00:00',
				 'shiftPagi'=>'19:59:00',
				 'shiftMalam'=>'07:59:00'],

	]; 

	$jamPulang['SLRO']=$jamPulang['MDHO'];
	$jamPulang['KBHE']=$jamPulang['KAME']=$jamPulang['DRME']=$jamPulang['BGRE'];



if($_GET['proses']=='getKry'){
	$optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$kdeOrg=$_POST['kdeOrg'];
	if(strlen($kdeOrg)>4)
	{
		$where=" subbagian='".$kdeOrg."'";
	}elseif($kdeOrg==''){
		$where=" lokasitugas='".$_SESSION['empl']['lokasitugas']."' ";
	}
	else
	{
		$where=" lokasitugas='".$kdeOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
	}

	$where.=" and (tanggalkeluar='' or  tanggalkeluar='0000-00-00') ";
	$sKry="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
	$qKry=mysql_query($sKry) or die(mysql_error());
	while($rKry=mysql_fetch_assoc($qKry))
	{
		$optKry.="<option value=".$rKry['karyawanid'].">".getNmKaryawan($rKry['karyawanid'])." - ".$rKry['nik']."</option>";
	}
	$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdeOrg."'";
	$qPeriode=mysql_query($sPeriode) or die(mysql_error());
	while($rPeriode=mysql_fetch_assoc($qPeriode))
	{
		$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
	}
	//echo $optPeriode;
	echo $optKry."###".$optPeriode;
}

elseif($_GET['proses']=='preview'){
	$tgl1=$_POST['tgl1'];
	$tgl2=$_POST['tgl2'];
	$tglDb1=tanggalDb($tgl1);
	$tglDb2=tanggalDb($tgl2);


	$sKry="select *,concat(namakaryawan,' ',nmtengah,' ',nmbelakang) as nama from ".$dbname.".datakaryawan where karyawanid='".$_POST['idKry']."'";
	$qKry=mysql_query($sKry) or die(mysql_error());
	$rKry=mysql_fetch_assoc($qKry);
	$kdOrg=$rKry['lokasitugas'];
	if(strlen($rKry['subbagian'])>4)
	{
		$kdOrg=$rKry['subbagian'];
	}
	$filter_kodeorg ="='".$kdOrg."' ";


	$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi ".$filter_kodeorg;
	$qOrg=mysql_query($sOrg) or die(mysql_error());
	$rOrg=mysql_fetch_assoc($qOrg);

	if(strtolower($kdOrg)=="mdho"){
		$thLateness="<td>Lateness";
		$thEarly="<td>Early";
	}else{
		$thLateness="";
		$thEarly="";
	}

	$table="
	<style>
		.smbr{
			display:none;
		}
	</style>
	<table>
				<tr>
						<td>NIK<td>:<td>".$rKry['nik']."
				</tr>
				<tr>
						<td>Nama Karyawan<td>:<td>".$rKry['nama']."
				</tr>
				<tr>
						<td>Unit Kerja<td>:<td>".$rOrg['namaorganisasi']."
				</tr>
				<tr>
						<td>Periode<td>:<td>".$tgl1." s/d ".$tgl2."
				</tr>

	</table>
	<table cellspacing=1 border=0 class=sortable>
	<thead class=rowheader>
			<td>No
			<td>Tanggal
			<td>Hari
			<td>Absen
			<td>Masuk (In)
			".$thLateness."
			<td>Keluar (Out)
			".$thEarly."
			<td>Keterangan
		</thead>
		<tbody>
	";
	


	/*$str="select * from ".$dbname.".sdm_absensidt where (tanggal between '".$tglDb1."'  and '".$tglDb2."') and karyawanid='".$_POST['idKry']."' order by tanggal asc,jam desc";// echo $str;exit();*/
	// echo $str;exit();

	$strAbsensi="select * from (SELECT *, timediff(jam,'".$jamMasuk[$kdOrg]['reguler']."') as lateness, timediff('".$jamPulang[$kdOrg]['reguler']."',jamPlg) as early FROM ".$dbname.".sdm_absensidt ORDER BY jam DESC) AS j  
	where (tanggal between '".$tglDb1."'  
	and '".$tglDb2."') and karyawanid='".$_POST['idKry']."' 
	GROUP BY tanggal
	order by tanggal asc,jam desc";

	$re=mysql_query($strAbsensi);
	$no=0;
	while($res=mysql_fetch_assoc($re))
	{
		$lateness = (strtotime($res['lateness']) > 0 ) ? $res['lateness'] : '-';
		$early = (strtotime($res['early'])>0) ? $res['early'] : '-';
		//$lateness = $tg
		$sShift="select keterangan from ".$dbname.".sdm_5absensi where kodeabsen='".$res['absensi']."'";
		$qShif=mysql_query($sShift) or die(mysql_error());
		$rShift=mysql_fetch_assoc($qShif);

		if(strtolower($res['penjelasan'])=="off"){
			$lateness="-";
			$early="-";
		}

		if(strtotime($res['jam'])>=strtotime($jamPulang[$kdOrg]['reguler'])){//jam masuk > jam pulang reguler (shift malam)
			$lateness=getTimeDiff($jamMasuk[$kdOrg]['shiftMalam'],$res['jam']);
			$early   =getTimeDiff($res['jamPlg'],$jamPulang[$kdOrg]['shiftMalam']);
		}

		$no+=1;

		if(strtolower($kdOrg)=="mdho"){
			$tdLateness="<td>".$lateness;
			$tdEarly="<td>".$early;
			
		}else{
			$tdLateness="";
			$tdEarly="";
		}

		$table.="
		<tr  class=rowcontent>
				<td>".$no."
				<td>".tglIndo($res['tanggal'])."
				<td>".getHari($res['tanggal'])."
				<td>".$rShift['keterangan']."
				<td>".$res['jam']."
				".$tdLateness."
				<td>".$res['jamPlg']."
				".$tdEarly."
				<td>".$res['penjelasan']."
				<td class='smbr'>".($res['sumber']=='F'?'Dari Fingerprint':'')."
		</tr>";
	}

	$table.="
		</tbody>
	</table>";

	echo $table;
}

elseif($_GET['proses']=='pdf'){
		//=============

			$tgl1=$_GET['tgl1'];
			$tgl2=$_GET['tgl2'];
			$tglDb1=tanggalDb($tgl1);
			$tglDb2=tanggalDb($tgl2);


			$sKry="select *,concat(namakaryawan,' ',nmtengah,' ',nmbelakang) as nama from ".$dbname.".datakaryawan where karyawanid='".$_GET['idKry']."'";
			$qKry=mysql_query($sKry) or die(mysql_error());
			$rKry=mysql_fetch_assoc($qKry);
			$kdOrg=$rKry['lokasitugas'];
			if(strlen($rKry['subbagian'])>4)
			{
				$kdOrg=$rKry['subbagian'];
			}
			$filter_kodeorg ="='".$kdOrg."' ";

			/*echo "<pre>";
			var_dump($_SESSION);
			echo "</pre>";*/
			//echo $filter_kodeorg;

		//create Header
		class PDF extends FPDF
		{

			function Header()
			{
			global $conn;
			global $dbname;
			global $userid;
			global $kdOrg;
			global $filter_kodeorg;
			global $tgl1;
			global $tglDb1;
			global $tgl2;
			global $tglDb2;
			global $rKry;

						$sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi ".$filter_kodeorg;
						$qInduk=mysql_query($sInduk) or die(mysql_error());
						$rInduk=mysql_fetch_assoc($qInduk);

					  // $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'";
					   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
					   $res1=mysql_query($str1) or die(mysql_error());
					   while($bar1=mysql_fetch_object($res1))
					   {
						 $nama=$bar1->namaorganisasi;
						 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
						 $telp=$bar1->telepon;
					   }

					   $sIsi="select * from ".$dbname.".sdm_absensiht where kodeorg ".$filter_kodeorg." and tanggal='".$tglDb."'";
					   $qIsi=mysql_query($sIsi) or die(mysql_error());
					   $rIsi=mysql_fetch_assoc($qIsi);

						$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi ".$filter_kodeorg;
						$qOrg=mysql_query($sOrg) or die(mysql_error());
						$rOrg=mysql_fetch_assoc($qOrg);


				$path='images/logo.jpg';
				$this->Image($path,15,5,60,20);
				$this->SetFont('Arial','B',20);
				$this->SetFillColor(255,255,255);
				$this->SetX(80);
				$this->Cell(60,5,'PT. NAFASINDO',0,1,'L');
				$this->SetX(80);
				$this->SetFont('Arial','B',15);
				$this->Cell(60,10,'LAPORAN KEHADIRAN KARYAWAN',0,1,'L');
				$this->Ln();
				$this->SetFont('Arial','B',9);
				$this->Cell(20,5,$namapt,'',1,'L');
				$this->SetFont('Arial','',9);
				$this->Line(10,30,200,30);

					$this->Cell(35,5,'NIK','',0,'L');
					$this->Cell(2,5,':','',0,'L');
					$this->Cell(75,5,$rKry['nik']);
					$this->SetFont('Arial','B',9);
					$this->Cell(25,5,'Periode','',0,'L');
					$this->Cell(2,5,'','',0,'L');
					$this->Cell(35,5,'',0,1,'L');

					$this->SetFillColor(255,255,255);
					$this->SetFont('Arial','',9);

					$this->Cell(35,5,'Nama Karyawan ','',0,'L');
					$this->Cell(2,5,':','',0,'L');
					$this->Cell(75,5,$rKry['nama']);
					$this->Cell(25,5,'Tanggal Mulai','',0,'L');
					$this->Cell(2,5,':','',0,'L');
					$this->Cell(35,5,$tgl1 ,0,1,'L');


					if(strlen($rOrg['kodeorganisasi'])=='4'){
						$rOrg['namaorganisasi']='KANTOR '.$rOrg['namaorganisasi'];
					}
					$this->Cell(35,5,'Unit Kerja','',0,'L');
					$this->Cell(2,5,':','',0,'L');
					$this->Cell(75,5,$rOrg['namaorganisasi']);
					$this->Cell(25,5,'Tanggal Sampai','',0,'L');
					$this->Cell(2,5,':','',0,'L');
					$this->Cell(35,5,$tgl2,0,1,'L');

					//		$this->Cell(140,5,' ','',0,'R');
					//			$this->Cell(140,5,' ','',0,'R');

				//	$this->Cell(35,5,'User','',0,'L');
		//			$this->Cell(2,5,':','',0,'L');
		//			$this->Cell(80,5,$_SESSION['standard']['username'],'',1,'L');


					//	        $this->Ln();
			 $this->Ln();

			}


			function Footer()
			{
				$this->SetY(-15);
				$this->SetFont('Arial','I',8);
				$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
			}

		}





			$pdf=new PDF('P','mm','A4');
			$pdf->AddPage();

			$pdf->Ln();

			$pdf->SetFont('Arial','U',15);
			$pdf->SetY(55);
			$pdf->Cell(190,5,'',0,1,'C');
			$pdf->Ln();
			$pdf->SetFont('Arial','B',9);
			$pdf->SetFillColor(220,220,220);
			$pdf->Cell(8,5,'No',1,0,'L',1);
			$pdf->Cell(20,5,'Tanggal',1,0,'C',1);
			$pdf->Cell(20,5,'Hari',1,0,'C',1);
			$pdf->Cell(25,5,'Absensi',1,0,'C',1);
			$pdf->Cell(20,5,'Masuk (In)',1,0,'C',1);
			if(strtolower($kdOrg)=='mdho'){
				$pdf->Cell(20,5,'Lateness',1,0,'C',1);
			}
			$pdf->Cell(20,5,'Keluar (Out)',1,0,'C',1);
			if(strtolower($kdOrg)=='mdho'){
				$pdf->Cell(20,5,'Early',1,0,'C',1);
			}
			$pdf->Cell(40,5,$_SESSION['lang']['keterangan'],1,1,'C',1);


			//$pdf->Cell(25,5,'Total',1,1,'C',1);
			$pdf->SetFillColor(255,255,255);
			$pdf->SetFont('Arial','',9);
				/*$str="select * from ".$dbname.".sdm_absensidt where (tanggal between '".$tglDb1."'  and '".$tglDb2."') and karyawanid='".$_GET['idKry']."' order by tanggal asc";*/
				// echo $str;exit();

				$strAbsensi="select * from (SELECT *, timediff(jam,'".$jamMasuk[$kdOrg]['reguler']."') as lateness, timediff('".$jamPulang[$kdOrg]['reguler']."',jamPlg) as early FROM ".$dbname.".sdm_absensidt ORDER BY jam DESC) AS j  
					where (tanggal between '".$tglDb1."'  
					and '".$tglDb2."') and karyawanid='".$_GET['idKry']."' 
					GROUP BY tanggal
					order by tanggal asc,jam desc";
					//exit($strAbsensi);
				$re=mysql_query($strAbsensi);
				$no=0;
				while($res=mysql_fetch_assoc($re))
				{

					$lateness = (strtotime($res['lateness']) > 0 ) ? $res['lateness'] : '-';
					$early = (strtotime($res['early'])>0) ? $res['early'] : '-';
					//$lateness = $tg
					$sShift="select keterangan from ".$dbname.".sdm_5absensi where kodeabsen='".$res['absensi']."'";
					$qShif=mysql_query($sShift) or die(mysql_error());
					$rShift=mysql_fetch_assoc($qShif);

					if(strtolower($res['penjelasan'])=="off"){
						$lateness="-";
						$early="-";
					}

					if(strtotime($res['jam'])>=strtotime($jamPulang[$kdOrg]['reguler'])){//jam masuk > jam pulang reguler (shift malam)
						$lateness=getTimeDiff($jamMasuk[$kdOrg]['shiftMalam'],$res['jam']);
						$early   =getTimeDiff($res['jamPlg'],$jamPulang[$kdOrg]['shiftMalam']);
					}


					$no+=1;
					$pdf->Cell(8,5,$no,1,0,'L',1);
					$pdf->Cell(20,5,tglIndo($res['tanggal']),1,0,'L',1);
					$pdf->Cell(20,5,getHari($res['tanggal']),1,0,'L',1);
					$pdf->Cell(25,5,$rShift['keterangan'],1,0,'C',1);
					$pdf->Cell(20,5,$res['jam'],1,0,'C',1);
					if(strtolower($kdOrg)=='mdho'){
						$pdf->Cell(20,5,$lateness,1,0,'C',1);
					}
					$pdf->Cell(20,5,$res['jamPlg'],1,0,'C',1);
					if(strtolower($kdOrg)=='mdho'){
						$pdf->Cell(20,5,$early,1,0,'C',1);
					}
					$pdf->Cell(40,5,$res['penjelasan'],1,1,'L',1);
				}


			$fileName ='lapAbsensi_'. $rKry['nama'].'_'.$tgl1.'_'.$tgl2.'.pdf';
			str_replace("'"," ",$fileName);
			$pdf->SetTitle($fileName);
			$pdf->Output($fileName,'I');
			//$pdf->Output();
	}

?>
