<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$tanggalmulai=$_POST['tanggalmulai'];
$tanggalsampai=$_POST['tanggalsampai'];
$noakun=$_POST['noakun'];
$kodeorg=$_POST['kodeorg'];

if($tanggalmulai==''){ echo "warning: silakan mengisi tanggal"; exit; }
if($tanggalsampai==''){ echo "warning: silakan mengisi tanggal"; exit; }
if($noakun==''){ echo "warning: silakan memilih no akun"; exit; }

$qwe=explode("-",$tanggalmulai); $tanggalmulai=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggalsampai); $tanggalsampai=$qwe[2]."-".$qwe[1]."-".$qwe[0];

$where=($kodeorg!='')? " a.kodeorg='".$kodeorg."'" : "a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi where induk ='NFS')";

#ambil saldo awal supplier
$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and kodesupplier!='' and kodesupplier is not null and kodesupplier!='0'
      and ".$where." group by a.kodesupplier
";
//exit("Error : ".$str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $sawal[$bar->kodesupplier]=$bar->sawal;
    $supplier[$bar->kodesupplier]=$bar->namasupplier;
    $akun[$bar->noakun]=$bar->namaakun;
}

#ambil saldo awal customer
/*$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodecustomer,c.namacustomer from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".pmn_4customer c on a.kodecustomer = c.kodecustomer
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and a.kodecustomer!='' and a.kodecustomer is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi where induk ='".$kodeorg."') group by a.kodecustomer
";*/

$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodecustomer as kodesupplier,c.namacustomer as namasupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".pmn_4customer c on a.kodecustomer = c.kodecustomer
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and a.kodecustomer!='' and a.kodecustomer is not null and a.kodecustomer!=0
      and ".$where." group by a.kodecustomer
";
//exit("Error : ".$str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $sawal[$bar->kodesupplier]=$bar->sawal;
    $supplier[$bar->kodesupplier]=$bar->namasupplier;
    $akun[$bar->noakun]=$bar->namaakun;
}

#ambil saldo awal  karyawan
/*$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by c.namakaryawan";*/
	  
$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null and a.nik!=0
      and ".$where." group by c.namakaryawan";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $sawal[$bar->kodesupplier]=$bar->sawal;
    $supplier[$bar->nik]=$bar->namakaryawan;
    $akun[$bar->noakun]=$bar->namaakun;
}

#ambil  transaksi dalam periode supplier
/*$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.noakun = '".$noakun."' and kodesupplier!='' and kodesupplier is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by a.kodesupplier
";
*/
$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.noakun = '".$noakun."' and kodesupplier!='' and kodesupplier is not null and kodesupplier!='0'
      and ".$where." group by a.kodesupplier
";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $debet[$bar->kodesupplier]=$bar->debet;
    $kredit[$bar->kodesupplier]=$bar->kredit;
    $supplier[$bar->kodesupplier]=$bar->namasupplier;
    $akun[$bar->noakun]=$bar->namaakun;
}


#ambil  transaksi dalam periode customer
/*$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodecustomer,c.namacustomer from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".pmn_4customer c on a.kodecustomer = c.kodecustomer
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.noakun = '".$noakun."' and a.kodecustomer!='' and a.kodecustomer is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by a.kodecustomer
";*/

$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodecustomer,c.namacustomer from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".pmn_4customer c on a.kodecustomer = c.kodecustomer
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.noakun = '".$noakun."' and a.kodecustomer!='' and a.kodecustomer is not null and a.kodecustomer!=0
      and ".$where." group by a.kodecustomer
";
//exit("Error : ".$str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $debet[$bar->kodecustomer]=$bar->debet;
    $kredit[$bar->kodecustomer]=$bar->kredit;
    $supplier[$bar->kodecustomer]=$bar->namacustomer;
    $akun[$bar->noakun]=$bar->namaakun;
}


#ambil saldo transaksi  karyawan
/*$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."'  
      and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by c.namakaryawan
";*/


$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."'  
      and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null and a.nik!=0
      and ".$where." group by c.namakaryawan
";

$res=mysql_query($str);

while($bar=mysql_fetch_object($res))
{
    $debet[$bar->nik]=$bar->debet;
    $kredit[$bar->nik]=$bar->kredit;
    $supplier[$bar->nik]=$bar->namakaryawan;
    $akun[$bar->noakun]=$bar->namaakun;
}


//=================================================
$no=0;
if($supplier<1)
{
        echo"<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
    foreach($supplier as $kdsupp =>$val){
		//update by ThIS 24/02/15 showing only 
		if ($sawal[$kdsupp]==0 and $debet[$kdsupp]==0 and $kredit[$kdsupp]==0){
			}else{
            $no+=1;
            echo"<tr class=rowcontent style='cursor:pointer;' title='Click to see detail'  onclick=lihatDetailHutang('".$kdsupp."','".$noakun."','".$tanggalmulai."','".$tanggalsampai."','".$kodeorg."',event)>
                  <td align=center width=20>".$no."</td>
                  <td align=center>".$kodeorg."</td>
                  <td>".$noakun."</td>
                  <td>".$akun[$noakun]."</td>
                  <td>".$val."</td>
                   <td align=right width=100>".number_format($sawal[$kdsupp],2)."</td>   
                  <td align=right width=100>".number_format($debet[$kdsupp],2)."</td>
                  <td align=right width=100>".number_format($kredit[$kdsupp],2)."</td>
                  <td align=right width=100>".number_format($sawal[$kdsupp]+$debet[$kdsupp]-$kredit[$kdsupp],2)."</td>
                 </tr>"; 
          $tsa+=$sawal[$kdsupp];
          $td+=$debet[$kdsupp];
          $tk+=$kredit[$kdsupp];
          $tak+=($sawal[$kdsupp]+$debet[$kdsupp]-$kredit[$kdsupp]);        
			}
    }	
} 
echo"<tr class=rowcontent>
      <td align=center colspan=5>Total</td>
       <td align=right width=100>".number_format($tsa,2)."</td>   
      <td align=right width=100>".number_format($td,2)."</td>
      <td align=right width=100>".number_format($tk,2)."</td>
      <td align=right width=100>".number_format($tak,2)."</td>
     </tr>"; 	

?>