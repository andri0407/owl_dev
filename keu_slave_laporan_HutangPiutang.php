<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
?>
	<link rel=stylesheet type=text/css href=style/generic.css>	
<?
$tanggalmulai=$_GET['mulai'];
$tanggalsampai=$_GET['sampai'];
$noakun=$_GET['noakun'];
$kodesupplier=$_GET['kodesupplier'];
$kodeorg=$_GET['kodeorg'];

if($tanggalmulai==''){ echo "warning: silakan mengisi tanggal"; exit; }
if($tanggalsampai==''){ echo "warning: silakan mengisi tanggal"; exit; }
if($noakun==''){ echo "warning: silakan memilih no akun"; exit; }

$where=($kodeorg!='')? " a.kodeorg='".$kodeorg."'" : "a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi where induk ='NFS')";

#ambil nama karyawan
$str="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$kodesupplier."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $supplier=$bar->namakaryawan;
}

$str="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $supplier=$bar->namasupplier;
}

#ambil saldo awal supplier
$str="select sum(a.debet-a.kredit) as sawal,a.noakun from ".$dbname.".keu_jurnaldt_vw a
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and (a.kodesupplier='".$kodesupplier."' or a.nik='".$kodesupplier."' or a.kodecustomer='".$kodesupplier."')
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."')";

$str="select sum(a.debet-a.kredit) as sawal,a.noakun from ".$dbname.".keu_jurnaldt_vw a
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and (a.kodesupplier='".$kodesupplier."' or a.nik='".$kodesupplier."' or a.kodecustomer='".$kodesupplier."')
      and ".$where."";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $sawal[$kodesupplier]=$bar->sawal;
}


#ambil  transaksi dalam periode supplier
$str="select a.debet  as debet, a.kredit as kredit,a.nojurnal,a.noreferensi,a.tanggal,a.noakun,a.keterangan, a.kodesupplier,a.kodeorg from ".$dbname.".keu_jurnaldt_vw a
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.noakun = '".$noakun."' and (a.kodesupplier='".$kodesupplier."' or a.nik='".$kodesupplier."' or a.kodecustomer='".$kodesupplier."')
      and ".$where." order by tanggal";
	 // echo $str;	  
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $dat[$bar->nojurnal]=$bar->tanggal;
    $ket[$bar->nojurnal]=$bar->nojurnal;
    $ref[$bar->nojurnal]=$bar->noreferensi;
    $debet[$bar->nojurnal]+=$bar->debet;
    $kredit[$bar->nojurnal]+=$bar->kredit;
	$kdorgdet[$bar->nojurnal]=$bar->kodeorg;
}

#ambil saldo transaksi  karyawan
$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.nojurnal,a.noreferensi,a.tanggal,a.keterangan,a.noakun,a.nik,a.kodeorg from ".$dbname.".keu_jurnaldt_vw a
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."'  
      and a.noakun = '".$noakun."' and a.nik='".$kodesupplier."'
      and ".$where." order by tanggal";
	  

	  
	  
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $dat[$bar->nojurnal]=$bar->tanggal;
    $ket[$bar->nojurnal]=$bar->nojurnal;
    $ref[$bar->nojurnal]=$bar->noreferensi;
    $debet[$bar->nojurnal]+=$bar->debet;
    $kredit[$bar->nojurnal]+=$bar->kredit;
	$kdorgdet[$bar->nojurnal]=$bar->kodeorg;
}
            echo"<table class=sortable cellspacing=1 border=0 width=100%>
             <thead>
                    <tr>
                          <td align=center width=50>".$_SESSION['lang']['nourut']."</td>
                          <td align=center>".$_SESSION['lang']['organisasi']."</td>
                          <td align=center>".$_SESSION['lang']['tanggal']."</td>    
                          <td align=center>".$_SESSION['lang']['notransaksi']."</td>
                          <td align=center>".$_SESSION['lang']['noreferensi']."</td>     
                          <td align=center>".$_SESSION['lang']['noakun']."</td>
                          <td align=center>Karyawan/Supplier</td>
                          <td align=center>".$_SESSION['lang']['saldoawal']."</td>                             
                          <td align=center>".$_SESSION['lang']['debet']."</td>
                          <td align=center>".$_SESSION['lang']['kredit']."</td>
                          <td align=center>".$_SESSION['lang']['saldoakhir']."</td>      
						  <td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>                            
                        </tr>  
                 </thead>
                 <tbody id=container>"; 
//=================================================
$no=0;
if(count($dat)<1)
{
        echo"<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
    $tsa=$sawal[$kodesupplier];
    foreach($dat as $notran =>$val){
            $no+=1;
            if($debet[$notran]!=0 or $kredit[$notran]!=0){
                echo"<tr class=rowcontent >
                      <td align=center width=20>".$no."</td>
                      <td align=center>".$kodeorg."</td>   
                      <td align=center>".tanggalnormal($val)."</td>                   
                      <td align=center>".$notran."</td>
                       <td align=center>".$ref[$notran]."</td>     
                      <td>".$noakun."</td>
                      <td>".$supplier."</td>
                       <td align=right width=100>".number_format($tsa,2)."</td>   
                      <td align=right width=100>".number_format($debet[$notran],2)."</td>
                      <td align=right width=100>".number_format($kredit[$notran],2)."</td>
                      <td align=right width=100>".number_format($tsa+$debet[$notran]-$kredit[$notran],2)."</td>
					   <td>".$kdorgdet[$notran]."</td>
                     </tr>"; 
              $tsa=$tsa+$debet[$notran]-$kredit[$notran];   
            }
    }	
} 
?>