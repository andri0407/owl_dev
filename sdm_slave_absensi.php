<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$proses=$_POST['proses'];
$txtFind=$_POST['txtfind'];
$absnId=explode("###",$_POST['absnId']);
$tgl=tanggalsystem($absnId[1]);
$kdOrg=$absnId[0];
$krywnId=$_POST['krywnId'];
$shifTid=$_POST['shifTid'];
$asbensiId=$_POST['asbensiId'];
$Jam=$_POST['Jam'];
$Jam2=$_POST['Jam2'];
$ket=$_POST['ket'];
$periode=$_POST['period'];
$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);

#get user/admin absensi maunual
$strUser="select * from ".$dbname.".setup_approval where applikasi='ABSEN' and karyawanid='".$_SESSION['empl']['karyawanid']."'";
$qUser=mysql_query($strUser) or die(mysql_error());
$manual=0;
if(mysql_num_rows($qUser)>0){
	$manual=1;
}

if($idOrg=='SLRO' || $idOrg=='MDHO'){
	$idOrg=substr($kdOrg,0,4);;
}
$catu=$_POST['catu'];



	switch($proses)
	{
		case'cariOrg':
		//echo"warning:masuk";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where namaorganisasi like '%".$txtFind."%' or kodeorganisasi like '%".$txtFind."%' "; //echo "warning:".$str;exit();
		if($res=mysql_query($str))
		{
			echo"
          <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; height:300px;\" >
        <table class=data cellspacing=1 cellpadding=2  border=0>
				 <thead>
				 <tr class=rowheader>
				 <td class=firsttd>
				 No.
				 </td>
				 <td>".$_SESSION['lang']['kodeorg']."</td>
				 <td>".$_SESSION['lang']['namaorganisasi']."</td>
				 </tr>
				 </thead>
				 <tbody>";
			$no=0;
			while($bar=mysql_fetch_object($res))
			{
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setOrg('".$bar->kodeorganisasi."','".$bar->namaorganisasi."')\" title='Click' >
					  <td class=firsttd>".$no."</td>
					  <td>".$bar->kodeorganisasi."</td>
					  <td>".$bar->namaorganisasi."</td>
					 </tr>";
			}
			echo "</tbody>
				  <tfoot>
				  </tfoot>
				  </table></div></fieldset>";
		  }
		  else
			{
				echo " Gagal,".addslashes(mysql_error($conn));
			}
		break;
		case'cariOrg2':
		//echo"warning:masuk";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where namaorganisasi like '%".$txtFind."%' or kodeorganisasi like '%".$txtFind."%' "; //echo "warning:".$str;exit();
		if($res=mysql_query($str))
		{
			echo"
          <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; height:300px;\" >
        <table class=data cellspacing=1 cellpadding=2  border=0>
				 <thead>
				 <tr class=rowheader>
				 <td class=firsttd>
				 No.
				 </td>
				 <td>".$_SESSION['lang']['kodeorg']."</td>
				 <td>".$_SESSION['lang']['namaorganisasi']."</td>
				 </tr>
				 </thead>
				 <tbody>";
			$no=0;
			while($bar=mysql_fetch_object($res))
			{
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setOrg2('".$bar->kodeorganisasi."','".$bar->namaorganisasi."')\" title='Click' >
					  <td class=firsttd>".$no."</td>
					  <td>".$bar->kodeorganisasi."</td>
					  <td>".$bar->namaorganisasi."</td>
					 </tr>";
			}
			echo "</tbody>
				  <tfoot>
				  </tfoot>
				  </table></div></fieldset>";
		  }
		  else
			{
				echo " Gagal,".addslashes(mysql_error($conn));
			}
		break;
		case'cekData':

		//SELECT * FROM `sdm_5periodegaji` WHERE `kodeorg`='SOGE' and `sudahproses`=0 and `tanggalmulai`<'20110112' and `tanggalsampai`>'20110112'
		$sCek="select DISTINCT tanggalmulai,tanggalsampai,periode from ".$dbname.".sdm_5periodegaji where kodeorg like '".substr($idOrg,0,5)."' and sudahproses=0 and tanggalmulai<='".$tgl."'";//" and tanggalsampai>='".$tgl."'";
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_num_rows($qCek);

		if($rCek>0)
		{

		$sCek="select kodeorg,tanggal from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg like '".$kdOrg."'"; //echo "warning".$sCek;nospb
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_fetch_row($qCek);
		if($rCek<1)
		{
			$sIns="insert into ".$dbname.".sdm_absensiht (`kodeorg`,`tanggal`,`periode`) values ('".$kdOrg."','".$tgl."','".$periode."')"; //echo"warning:".$sIns;
			if(mysql_query($sIns))
			{
				$sDetIns="insert into ".$dbname.".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `penjelasan`,`catu`) values ('".$kdOrg."','".$tgl."','".$krywnId."','".$shifTid."','".$asbensiId."','".$Jam."','".$Jam2."','".$ket."',".$catu.")";
				if(mysql_query($sDetIns))
				{
					echo"";
				}
				else
				{echo "DB Error : ".mysql_error($conn);}
			}
			else
			{
				echo "DB Error : ".mysql_error($conn);
			}
		}
		else
		{
			$sDetIns="insert into ".$dbname.".sdm_absensidt (`kodeorg`,`tanggal`, `karyawanid`, `shift`, `absensi`, `jam`,`jamPlg`, `penjelasan`,`catu`) values ('".$kdOrg."','".$tgl."','".$krywnId."','".$shifTid."','".$asbensiId."','".$Jam."','".$Jam2."','".$ket."',".$catu.")";
				//echo "warning:test".$dins;
				if(mysql_query($sDetIns))
				{
					echo"";
				}
				else
				{
				//echo "warning:masuk";
				echo "DB Error : ".mysql_error($conn);
				}
		}
//                exit(" Error:".$sDetIns);
		}
		else
		{
			echo"warning:Diluar Periode Gaji";
			exit();
		}
		break;
		case'loadNewData':

		$org=$_SESSION['empl']['lokasitugas'];
		if($_POST['kodeorg']!=''){
			$org=$_POST['kodeorg'];
			$idOrg=substr($_POST['kodeorg'],0,4);
		}
		echo"
		<table cellspacing=2 cellpadding=5 border=0 class=sortable>
		<thead>
		<tr class=rowheader>
		<td>No.</td>
		<td>".$_SESSION['lang']['kodeorg']."</td>
		<td>Nama Organisasi</td>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>Action</td>
		</tr>
		</thead>
		<tbody>
		";
		$limit=20;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}

		if($_POST['tgl']!='')
		{
				$bln=explode("-",$_POST['tgl']);

				$where.=" and tanggal='".$bln[2]."-".$bln[1]."-".$bln[0]."'";
		}

		$offset=$page*$limit;
		$maxdisplay=($page*$limit);//ind
		$ql2="select count(*) as jmlhrow from ".$dbname.".sdm_absensiht where substring(kodeorg,1,4)='".$org."' ".$where." order by `tanggal` desc";// echo $ql2;
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}


		$slvhc="select * from ".$dbname.".sdm_absensiht where substring(kodeorg,1,4)='".$org."'  ".$where." order by `tanggal` desc limit ".$offset.",".$limit."";
		$qlvhc=mysql_query($slvhc) or die(mysql_error());
		$user_online=$_SESSION['standard']['userid'];
		$no=$maxdisplay;//ind
		while($rlvhc=mysql_fetch_assoc($qlvhc))
		{
			$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
			$qOrg=mysql_query($sOrg) or die(mysql_error());
			$rOrg=mysql_fetch_assoc($qOrg);


		/*$sGp="select DISTINCT sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg like '".$rlvhc['kodeorg']."' and `periode`='".$rlvhc['periode']."'";
		$qGp=mysql_query($sGp) or die(mysql_error());
		$rGp=mysql_fetch_assoc($qGp);*/

		$i="select * from ".$dbname.".setup_periodeakuntansi where periode like '%".substr($rlvhc['periode'],1,7)."%' and kodeorg ='".substr($rlvhc['kodeorg'],0,4)."' ";
		//echo $i;
		$n=mysql_query($i) or die (mysql_error($conn));
		$d=mysql_fetch_assoc($n);

		$no+=1;
		echo"
		<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$rlvhc['kodeorg']."</td>
		<td>".$rOrg['namaorganisasi']."</td>
		<td>".tanggalnormal($rlvhc['tanggal'])."</td>
		<td>".substr(tanggalnormal($rlvhc['periode']),1,7)."</td>
		<td align=center>";
		if($d['tutupbuku']==0)
		{
		echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['periode']."');\">
		<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >
		<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
		}
		else
		{
			echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
		}
		echo"</td>
		</tr>
		";
		}
		echo"
		<tr class=rowheader><td colspan=5 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";
		break;
		case'delData':
		$sCek="select posting from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg like '".$kdOrg."'"; //echo "warning".$sCek;;
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_fetch_assoc($qCek);
		if($rCek['posting']=='1')
		{
			echo"warning:Already Post This Data";
			exit();
		}
		$sDel="delete from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg like '".$kdOrg."'";// echo "___".$sDel;exit();
		if(mysql_query($sDel))
		{
			$sDelDetail="delete from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' and kodeorg like '".$kdOrg."'";
			if(mysql_query($sDelDetail))
			echo"";
			else
			echo "DB Error : ".mysql_error($conn);
		}
		else
		{echo "DB Error : ".mysql_error($conn);}

		break;
		case'cekHeader':
		//echo"warning:masuk";
		 $sCek="select DISTINCT tanggalmulai,tanggalsampai,periode from ".$dbname.".sdm_5periodegaji where kodeorg like '".$idOrg."' and periode='".$periode."' and sudahproses=0 and tanggalmulai<='".$tgl."' and tanggalsampai>='".$tgl."'";
                //    $sCek="select DISTINCT tanggalmulai,tanggalsampai,periode from ".$dbname.".sdm_5periodegaji where kodeorg like '".$_SESSION['empl']['lokasitugas']."' and periode='".$periode."' and sudahproses=0";
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_num_rows($qCek);
                //$rCek=mysql_fetch_assoc($qCek);
		if($rCek<1)
               // if($rCek['tanggalmulai']<=$tgl || $rCek['tanggalsampai']>=$tgl)
		{
			echo"warning:Tanggal Diluar Periode Gaji";
			exit();
		}
                //echo"warning:masuk".$aktif;exit();
		$sCek="select kodeorg,tanggal from ".$dbname.".sdm_absensiht where tanggal='".$tgl."' and kodeorg like '".$kdOrg."'"; //echo "warning".$sCek;nospb
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_fetch_row($qCek);
		if($rCek>0)
		{
			echo"warning:This Date And Organization Name Already Input";
			exit();
		}


                $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and
                kodeorg like '".$idOrg."' and tutupbuku=1";
               // exit("Error".$str) ;
                $res=mysql_query($str);
                if(mysql_num_rows($res)>0)
                $aktif=true;
                else
                $aktif=false;
                if($aktif==true)
                {
                	exit("Error:Periode sudah tutup buku");
                }
		break;
		case'cariAbsn':


		//echo 'masuk';
		echo"
		<div style=overflow:auto; height:350px;>
		<table cellspacing=2 cellpadding=5 border=0 class=sortable>
		<thead>
		<tr class=rowheader>
		<td>No.</td>
		<td>".$_SESSION['lang']['kodeorg']."</td>
		<td>Nama Organisasi</td>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>Action</td>
		</tr>
		</thead>
		<tbody>
		";
		//echo"warning:".$tgl."___".$kdOrg;
                    if($kdOrg!='')
                    {
                        $where.=" and kodeorg like '".$kdOrg."'";
                    }
                    if($tgl!='')
                    {
                        $bln=explode("-",$absnId[1]);

                        $where.=" and tanggal='".$bln[2]."-".$bln[1]."-".$bln[0]."'";
                    }

		$sCek="select * from ".$dbname.".sdm_absensiht where substr(kodeorg,1,4) like '".$idOrg."' ".$where."";//echo "warning".$sCek;exit();
                //echo $sCek;
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_num_rows($qCek);
		if($rCek>0)
		{
			$limit=20;
			$page=0;
			if(isset($_POST['page']))
			{
			$page=$_POST['page'];
			if($page<0)
			$page=0;
			}
			$offset=$page*$limit;
			$maxdisplay=($page*$limit);//ind


			$slvhc="select * from ".$dbname.".sdm_absensiht where substr(kodeorg,1,4) like '".$idOrg."' ".$where."  order by `tanggal` desc  limit ".$offset.",".$limit;
			$qlvhc=mysql_query($slvhc) or die(mysql_error());
			$user_online=$_SESSION['standard']['userid'];
			while($rlvhc=mysql_fetch_assoc($qlvhc))
			{
				$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
				$qOrg=mysql_query($sOrg) or die(mysql_error());
				$rOrg=mysql_fetch_assoc($qOrg);

		/*$sGp="select DISTINCT sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg like '".$rlvhc['kodeorg']."' and `periode`='".$rlvhc['periode']."'";
		$qGp=mysql_query($sGp) or die(mysql_error());
		$rGp=mysql_fetch_assoc($qGp);*/

		$i="select * from ".$dbname.".setup_periodeakuntansi where periode like '%".substr($rlvhc['periode'],1,7)."%' and kodeorg ='".substr($rlvhc['kodeorg'],0,4)."' ";
		//echo $i;
		$n=mysql_query($i) or die (mysql_error($conn));
		$d=mysql_fetch_assoc($n);


			$no+=1;
		echo"
		<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$rlvhc['kodeorg']."</td>
		<td>".$rOrg['namaorganisasi']."</td>
		<td>".tanggalnormal($rlvhc['tanggal'])."</td>
		<td>".substr(tanggalnormal($rlvhc['periode']),1,7)."</td>
		<td align=center>";
		if($d['tutupbuku']==0)
		{
		echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['periode']."');\">
		<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" >";
		echo" <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
		}
		else
		{
			echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_absensiht','".$rlvhc['kodeorg'].",".tanggalnormal($rlvhc['tanggal'])."','','sdm_absensiPdf',event)\">";
		}
		echo"</td>
		</tr>
		";
			}

			echo"
			<tr class=rowheader><td colspan=5 align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $rCek."<br />
			<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
			</tr>";
			echo"</tbody></table></div>";
		}
		else
		{
			echo"<tr class=rowcontent><td colspan=5 align=center>Not Found</td></tr></tbody></table></div>";
		}
		break;
		case'updateData':
		#jika user memiliki hak input kehadiran [Hadir] Manual
		if($manual==1 and $asbensiId=='H'){
			$ket.=" [absen manual by: ".$_SESSION['empl']['name']."]";
		}

		$sUpd="update ".$dbname.".sdm_absensidt set shift='".$shifTid."',absensi='".$asbensiId."',jam='".$Jam."',jamPlg='".$Jam2."',penjelasan='".$ket."',catu=".$catu." where kodeorg like '".$kdOrg."' and tanggal='".$tgl."' and karyawanid='".$krywnId."'";
		$datakaryawan=getDatakaryawan($krywnId);

		#hasil mesin fingerprint hanya bisa diedit keterangannya saja  
		if($datakaryawan['tipekaryawan']<=5 && $asbensiId=='H' && $manual!=1){
				$sUpd="update ".$dbname.".sdm_absensidt set penjelasan='".$ket."' where kodeorg like '".$kdOrg."' and tanggal='".$tgl."' and karyawanid='".$krywnId."'";
		}


			if(mysql_query($sUpd))
			echo"";
			else
			echo "DB Error : ".mysql_error($conn);
		break;
		case'delDetail':
			$sDelDetail="delete from ".$dbname.".sdm_absensidt where tanggal='".$tgl."' and kodeorg like '".$kdOrg."' and karyawanid='".$krywnId."'";
			if(mysql_query($sDelDetail))
			echo"";
			else
			echo "DB Error : ".mysql_error($conn);
		break;
		default:
		break;
	}


	function getDatakaryawan($karyawanid){
		global $dbname;
		$s="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$q=mysql_query($s) or die(mysql_error());
		return mysql_fetch_assoc($q);
	}

?>
