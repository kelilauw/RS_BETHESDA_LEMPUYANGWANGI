<?php
session_start();
require_once("../lib/dbconn.php");
require_once("../lib/setting.php");
?>
<HTML>
    <HEAD>
        <!--<TITLE>::: Sistem Informasi <?php echo $RS_NAME; ?> :::</TITLE>-->
        <TITLE></TITLE>
        
        <SCRIPT LANGUAGE="JavaScript">
            <!-- Begin
            function printWindow() {
                bV = parseInt(navigator.appVersion);
                if (bV >= 4) window.print();
            }
            //  End -->
        </script>
    </HEAD>
    <BODY TOPMARGIN=0 LEFTMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0 />
    <?
    $reg            = $_GET["rg"];
    $tgl_sekarang   = date("d-m-Y H:i:s", time());
    $tgl_now        = date("d-m-Y", time());
    $noUrut = 0;
   $rt = pg_query($con,
        "SELECT a.id as code, to_char(a.tanggal_reg,'DD MONTH YYYY') AS tanggal_reg, a.waktu_reg, ".
        "    a.mr_no, e.nama, to_char(e.tgl_lahir, 'DD MONTH YYYY') AS tgl_lahir, ".
        "    e.tmp_lahir, e.jenis_kelamin, f.tdesc AS agama, ".
        "    e.alm_tetap, e.kota_tetap, e.pos_tetap, e.tlp_tetap, ".
        "    a.id_penanggung, b.tdesc AS penanggung, a.id_penjamin, ".
        "    c.tdesc AS penjamin, a.no_jaminan, a.rujukan, a.rujukan_rs_id, ".
        "    d.tdesc AS rujukan_rs, a.rujukan_dokter, a.rawat_inap, ".
        "    a.status, a.tipe, g.tdesc AS tipe_pasien, a.diagnosa_sementara, ".
        "    to_char(a.tanggal_reg, 'DD MONTH YYYY') AS tanggal_reg_str, ".
        "        CASE ".
        "            WHEN a.rawat_inap = 'I' THEN 'Rawat Inap'  ".
        "            WHEN a.rawat_inap = 'Y' THEN 'Rawat Jalan' ".
        "            ELSE 'IGD' ".
        "        END AS rawatan, ".
        "        age(a.tanggal_reg , e.tgl_lahir ) AS umur, ".
	"	case when a.rujukan = 'Y' then 'Rujukan' ".
	"	     when a.rujukan ='U' then 'Unit Lain'  else 'Non-Rujukan' ".
        "       end as datang,  ".
        "   i.tdesc as  poli ".
        "FROM rs00006 a ".
        "   LEFT JOIN rs00001 b ON a.id_penanggung = b.tc AND b.tt = 'PEN'".
        "   LEFT JOIN rs00001 c ON a.id_penjamin = c.tc AND c.tt = 'PJN' ".
        "   LEFT JOIN rs00002 e ON a.mr_no = e.mr_no ".
        "   LEFT JOIN rs00001 f ON e.agama_id = f.tc AND f.tt = 'AGM' ".
        "   LEFT JOIN rs00001 g ON a.tipe = g.tc AND g.tt = 'JEP' ".
        "   LEFT JOIN rs00001 d ON a.id_penjamin = d.tc AND d.tt = 'RUJ' ".
        "   LEFT JOIN rs00001 h ON a.jenis_kedatangan_id = h.tc and h.tt = 'JDP' ".
	"   left join rs00001 i on i.tc_poli = a.poli ".
	"WHERE a.id = '$reg'");

    $nt = pg_num_rows($rt);
    $dt = pg_fetch_object($rt);
    if(isset($_GET['max_cetak'])){
		$selected_obat = implode(',',$_GET['cetak']);
	}
    $rowsPemakaianObat      = pg_query($con, "SELECT id, tanggal_entry, item_id, qty, tagihan, referensi, dibayar_penjamin, diskon 
                             FROM rs00008
                             WHERE trans_type = 'OB1' AND rs00008.no_reg = '".$_GET["rg"]."' AND id IN ($selected_obat) order by id ");
    $rowsPemakaianRacikan   = pg_query($con, "SELECT id, tanggal_entry, item_id, qty, tagihan, referensi, dibayar_penjamin, diskon 
                             FROM rs00008 
                             WHERE trans_type = 'RCK' AND rs00008.no_reg = '".$_GET["rg"]."' AND id IN ($selected_obat) order by id ");
	$rowsPemakaianBHP      = pg_query($con, "SELECT id, tanggal_entry, item_id, qty, tagihan, COALESCE(0,referensi::integer), dibayar_penjamin, diskon 
                             FROM rs00008
                             WHERE trans_type = 'BHP' AND rs00008.no_reg = '".$_GET["rg"]."' AND id IN ($selected_obat) order by id ");
	$rowsAdministrasi      = pg_query($con, "SELECT id, tanggal_entry, waktu_entry, item_id, qty, tagihan, referensi, dibayar_penjamin, diskon  , diskon 
                             FROM rs00008 
                             WHERE trans_type = 'ADA' AND rs00008.no_reg = '".$_GET["rg"]."' order by id ");
	
	//start sql nama relasi
	$rr = pg_query($con, "SELECT tc AS nama_relasi_id, tdesc AS nama_relasi
            FROM rs00008
            JOIN rs00001 ON rs00001.tc::text = rs00008.item_id::text AND rs00001.tt::text = 'RAP'
            WHERE trans_type = 'OBM' AND rs00008.no_reg = '".$_GET["rg"]."'");

    $nr = pg_num_rows($rr);
    $dr = pg_fetch_object($rr);
	//end sql nama relasi
	//start sql nama dokter
	$rr1 = pg_query($con, "SELECT b.id, b.nama
			FROM rs00008 a
			JOIN rs00017 b ON b.id = a.no_kwitansi
			WHERE a.no_reg = '".$_GET["rg"]."' and a.trans_type::text = 'OBM'::text ");
	$nr1 = pg_num_rows($rr1);
	$dr1 = pg_fetch_object($rr1);
	//---------------------------------
	$rr2 = pg_query($con, "SELECT b.id, b.nama
			FROM rs00006 a
			JOIN rs00017 b ON b.nama = a.diagnosa_sementara
			WHERE a.id = '".$_GET["rg"]."'");
	$nr2 = pg_num_rows($rr2);
	$dr2 = pg_fetch_object($rr2);
	if($nr1 > 0){
			$nama_dokter = $dr1->nama;
	} else if($nr1 == 0){
			$nama_dokter = $dt->diagnosa_sementara;
	} else {
			$nama_dokter = $dr2->nama;
	}
	//end sql nama dokter
	//start sql nomor resep
	$rr3 = pg_query($con, "SELECT a.nmr_transaksi, a.iter_resep
			FROM rs00008 a
			WHERE a.no_reg = '".$_GET["rg"]."' and a.trans_type::text = 'OBM'::text ");
	$nr3 = pg_num_rows($rr3);
	$dr3 = pg_fetch_object($rr3);
	$nomorResep = $dr3->nmr_transaksi; 
	$iterResep = $dr3->iter_resep; 
	//end sql nomor resep
	
	//start header kwitansi
	$rawatan = $dt->rawatan;
	// ambil bangsal
	$id_max = getFromTable("select max(id) from rs00010 where no_reg = '".$_GET["rg"]."'");
	if (!empty($id_max)) {
	$bangsal = getFromTable("select c.bangsal || ' / ' || e.tdesc ".
					"from rs00010 as a ".
					"    join rs00012 as b on a.bangsal_id = b.id ".
					"    join rs00012 as c on c.hierarchy = substr(b.hierarchy,1,6) || '000000000' ".
					"    join rs00001 as e on c.klasifikasi_tarif_id = e.tc and e.tt = 'KTR' ".
					"where a.id = '$id_max'");
	}

	if ($rawatan == "Rawat Inap") {
		$dirawat = 'RAWAT INAP '.'<br>'.$bangsal;
	} else if ($rawatan == "Rawat Jalan") {
		$dirawat = 'RAWAT JALAN '.'<br>'.$dt->poli;
	} else {
		$dirawat = 'RAWAT JALAN '.'<br>'.$dt->poli;
	}
	//end header kwitansi
	
    ?>
<table width="47%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 14px; letter-spacing: 2px;">
		<tr valign="middle" >
			<td rowspan="2" align="center"><!--<img width="70px" height="70px" src="../images/logo_kotakab_sragen.png" align="left"/>-->
			<font color=white>
				<div style="font-family: Tahoma; font-size: 12px; color: #000; padding-left: 8px; padding-right: 8px;">&nbsp</div>
			    <div style="font-family: Tahoma; font-size: 12px; color: #000; padding-left: 8px; padding-right: 8px; font-weight: bold"><?=$set_header[0]?></div><?php
			    $header = explode('-',$set_header[2]);
			    ?>
				<div style="font-family: Tahoma; font-size: 12px; color: #000; padding-left: 8px; padding-right: 8px; font-weight: bold"><?=$header[0]?></div>
				<div style="font-family: Tahoma; font-size: 12px; color: #000; padding-left: 8px; padding-right: 8px; font-weight: bold"><?=$header[1]?> - <?=$header[2]?></div>
				<div style="font-family: Tahoma; font-size: 12px; color: #000; padding-left: 8px; padding-right: 8px; font-weight: bold"><?=$set_header[3]?></div>
			</font>
		</tr>		
</table>
<table width="47%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;">
    <tr>
        <td colspan="6" style="border-top:2px solid;">&nbsp;</td>
    </tr>
    <tr>
        <td align="center" colspan="6" style="font-family: Tahoma; font-size: 11px; letter-spacing: 3px;"><b>RINCIAN TRANSAKSI FARMASI <?php echo $dirawat;?></b></td>
    </tr>
</table>

<table width="50%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;">
    <tr>
        <td valign="top">No.Res</td>
        <td colspan="3">: <? echo "<b>".$nomorResep."</b>"; ?></td>
		<?php if ($iterResep != '0') {?>
		<td colspan="2" align="right"><? echo "<b>ITER : ".$iterResep."X</b>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"; ?></td>
		<?php } else {?>
		<td colspan="2" align="right"><? echo "<b></b>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"; ?></td>
		<?php }?>
	</tr>
    <tr>
        <td valign="top">No.Reg</td>
        <td width="25%">: <? echo $dt->code; ?></td>
        <td colspan="4"><? echo $dt->tanggal_reg.' '.$dt->waktu_reg; ?></td>
    </tr>
    <tr>
        <td valign="top">No.RM</td>
        <td width="25%">: <? echo $dt->mr_no; ?></td>
        <td colspan="4"><b><? echo $dr->nama_relasi; ?></b></td>
    </tr>
    <tr>
        <td valign="top">Nama</td>
        <td colspan="5">: <? echo $dt->nama; ?></td>
    </tr>
    <tr>
        <td valign="top">Telp.</td>
        <td colspan="5">: <? echo $dt->tlp_tetap; ?></td>
    </tr>
    <tr>
        <td valign="top">Alamat</td>
        <td colspan="5">: <? echo $dt->alm_tetap; ?>, <? echo $dt->kota_tetap; ?></td>
    </tr>
    <tr>
        <td valign="top">Dokter</td>
        <td colspan="5">: <? echo $nama_dokter; ?></td>
    </tr>
    <tr>
        <td colspan="6">&nbsp;</td>
    </tr>
</table>
<table width="47%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 1px; letter-spacing: 2px;">
    <tr>
        <td colspan="6" width="45%" align="left" style='border-top:solid 1px #000;border-bottom:solid 1px #000;'>&nbsp;</td>
    </tr>
</table>
<table width="47%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;">
    <tr>
        <td width="1%" align="left" style='border-top:solid 1px #000;border-bottom:solid 1px #000;'>No.</td>
        <td width="37%" align="left" style='border-top:solid 1px #000;border-bottom:solid 1px #000;'>Nama Obat</td>
		 <?php
		//if ($dt->tipe_pasien=='Karyawan RS') {
        //echo "<td width='37%' align='right' style='border-top:solid 1px #000;border-bottom:solid 1px #000;' width='10%'>Harga Obat (Netto)</td>";
		//}
		?>
        <td width="3%" align="right" style='border-top:solid 1px #000;border-bottom:solid 1px #000;' width="10%">Jml</td>
        <td width="3%" align="right" style='border-top:solid 1px #000;border-bottom:solid 1px #000;' width="12%">Tagihan</td>
        <td width="3%" align="right" style='border-top:solid 1px #000;border-bottom:solid 1px #000;' width="12%">Penjamin</td>
        <td width="3%" align="right" style='border-top:solid 1px #000;border-bottom:solid 1px #000;' width="12%">Selisih</td>
        <td width="3%" align="right" style='border-top:solid 1px #000;border-bottom:solid 1px #000;' width="12%">Diskon</td>
    </tr>
<?php
if(pg_num_rows($rowsPemakaianObat) > 0){
    echo '<tr><td class="" colspan="6"><span style="font-weight: bold;">Obat Resep</span></td></tr>';
        $iObat          = 0;
        $total          = 0;
        $totalPenjamin  = 0;
        $totalSelisih   = 0;
        $totalDiskon   = 0;
        while($row=pg_fetch_array($rowsPemakaianObat)){
            $noUrut++;
            $iObat++;
            $total          = $total + $row["tagihan"];
            $totalPenjamin  = $totalPenjamin + $row["dibayar_penjamin"];
            $totalSelisih   = $totalSelisih + ($row["tagihan"]-$row["dibayar_penjamin"]);
            $totalDiskon  = $totalDiskon + $row["diskon"];
            $sqlObat = pg_query($con, "SELECT DISTINCT rs00015.id, rs00015.obat, rs00001.tdesc AS satuan, rs00016.harga 
                                        FROM rs00015 
                                        INNER JOIN rs00001 ON rs00015.satuan_id = rs00001.tc 
                                        INNER JOIN rs00016 ON rs00015.id = rs00016.obat_id 
                                        WHERE rs00001.tt = 'SAT' AND rs00015.id = ". $row["item_id"] );
            $obat = pg_fetch_array($sqlObat);
?>
	<tr>
	    <td valign="top" class="" align="left" height="15" ><?=$noUrut?>.</td>
		<td class="" align="left" height="15" ><?=$obat["obat"]?></td>
		<?php
		//if ($dt->tipe_pasien=='Karyawan RS') {
		//echo "<td class='' align='right' height='15' style='text-align: right'> ".number_format($obat[harga],'0','','.')."</td>";
		//}
		?>
		<td class="" align="right" height="15" style="text-align: right;"><?=$row["qty"]?> <? //=$obat["satuan"]?></td>
		<td class="" align="right" height="15" ><?=number_format($row["tagihan"],'0','','.')?></td>
		<td class="" align="right" height="15" ><?=number_format($row["dibayar_penjamin"],'0','','.')?></td>
		<td class="" align="right" height="15" ><?=number_format(($row["tagihan"]-$row["dibayar_penjamin"]),'0','','.')?>&nbsp;</td>
		<td class="" align="right" height="15" ><?=number_format($row["diskon"],'0','','.')?></td>
	</tr>
<?php
        }
}
?>
    <?php
if(pg_num_rows($rowsPemakaianRacikan) > 0){   
    echo '<tr><td class="" colspan="6"><span style="font-weight: bold;"><br/>Obat Racikan</span></td></tr>';
        $iRacikan       = 0;
        while($rowRacikan=pg_fetch_array($rowsPemakaianRacikan)){
            $noUrut++;
            $iRacikan++;
            $total          = $total + $rowRacikan["tagihan"];
            $totalPenjamin  = $totalPenjamin + $rowRacikan["dibayar_penjamin"];
            $totalSelisih   = $totalSelisih + ($rowRacikan["tagihan"]-$rowRacikan["dibayar_penjamin"]);
            $totalDiskon  = $totalDiskon + $rowRacikan["diskon"];
            $sqlObatR = pg_query($con, "SELECT DISTINCT rs00015.id, rs00015.obat, rs00001.tdesc AS satuan, rs00016.harga 
                                        FROM rs00015 
                                        INNER JOIN rs00001 ON rs00015.satuan_id = rs00001.tc 
                                        INNER JOIN rs00016 ON rs00015.id = rs00016.obat_id 
                                        WHERE rs00001.tt = 'SAT' AND rs00015.id = ". $rowRacikan["item_id"] );
            $obatR = pg_fetch_array($sqlObatR);
?>
	<tr>
	    <td valign="top" class="" align="left" height="15" ><?=$noUrut?>.</td>
		<td class="" align="left" height="15"><?=$obatR["obat"]?></td>
		<td class="" align="right" height="15" style="text-align: right;"><?=$rowRacikan["qty"]?> <? //=$obatR["satuan"]?></td>
		<td class="" align="right" height="15"><?=number_format($rowRacikan["tagihan"],'0','','.')?></td>
		<td class="" align="right" height="15"><?=number_format($rowRacikan["dibayar_penjamin"],'0','','.')?></td>
		<td class="" align="right" height="15"><?=number_format(($rowRacikan["tagihan"]-$rowRacikan["dibayar_penjamin"]),'0','','.')?>&nbsp;</td>
		<td class="" align="right" height="15"><?=number_format($rowRacikan["diskon"],'0','','.')?></td>
	</tr>
<?php
        }
}

if(pg_num_rows($rowsPemakaianBHP) > 0){   
    echo '<tr><td class="" colspan="6"><span style="font-weight: bold;"><br/>BHP</span></td></tr>';
        $iRacikan       = 0;
        while($rowRacikan=pg_fetch_array($rowsPemakaianBHP)){
            $noUrut++;
            $iRacikan++;
            $total          = $total + $rowRacikan["tagihan"];
            $totalPenjamin  = $totalPenjamin + $rowRacikan["dibayar_penjamin"];
            $totalSelisih   = $totalSelisih + ($rowRacikan["tagihan"]-$rowRacikan["dibayar_penjamin"]);
            $totalDiskon  = $totalDiskon + $rowRacikan["diskon"];
            $sqlObatR = pg_query($con, "SELECT DISTINCT rs00015.id, rs00015.obat, rs00001.tdesc AS satuan, rs00016.harga 
                                        FROM rs00015 
                                        INNER JOIN rs00001 ON rs00015.satuan_id = rs00001.tc 
                                        INNER JOIN rs00016 ON rs00015.id = rs00016.obat_id 
                                        WHERE rs00001.tt = 'SAT' AND rs00015.id = ". $rowRacikan["item_id"] );
            $obatR = pg_fetch_array($sqlObatR);
?>
	<tr>
	    <td valign="top" class="" align="left" height="15" ><?=$noUrut?>.</td>
		<td class="" align="left" height="15"><?=$obatR["obat"]?></td>
		<td class="" align="right" height="15" style="text-align: right;"><?=$rowRacikan["qty"]?> <? //=$obatR["satuan"]?></td>
		<td class="" align="right" height="15"><?=number_format($rowRacikan["tagihan"],'0','','.')?></td>
		<td class="" align="right" height="15"><?=number_format($rowRacikan["dibayar_penjamin"],'0','','.')?></td>
		<td class="" align="right" height="15"><?=number_format(($rowRacikan["tagihan"]-$rowRacikan["dibayar_penjamin"]),'0','','.')?>&nbsp;</td>
		<td class="" align="right" height="15"><?=number_format($rowRacikan["diskon"],'0','','.')?></td>
	</tr>
<?php
        }
}
?>  
	<tr>
		<td style='border-top:solid 1px #000;' colspan="3" align="right"><span style="font-weight: bold; font-size: 11px;">Total =</span></td>
		<td style='border-top:solid 1px #000;' align="right" ><span style="font-weight: bold;"><?= number_format($total,'0','','.')?></span></td>
		<td style='border-top:solid 1px #000;' align="right" ><span style="font-weight: bold;"><?= number_format($totalPenjamin,'0','','.')?></span></td>
		<td style='border-top:solid 1px #000;' align="right" ><span style="font-weight: bold;"><?= number_format($totalSelisih,'0','','.')?></span>&nbsp;</td>
		<td style='border-top:solid 1px #000;' align="right" ><span style="font-weight: bold;"><?= number_format($totalSelisih-$totalDiskon,'0','','.')?></span></td>
	</tr>
</table>

<table width="47%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 1px; letter-spacing: 2px;">
    <tr>
        <td colspan="6" width="45%" align="left" style='border-top:solid 1px #000;border-bottom:solid 1px #000;'>&nbsp;</td>
    </tr>
</table>
<table width="47%" border="0" cellpadding="0" cellspacing="0" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;">
    <tr>    
        <td width="50%" colspan="6" align="left" class="TITLE_SIM3">&nbsp;</td>
    </tr>
    <tr>
        <td width="35%" colspan="4" align="center" class="TITLE_SIM3">&nbsp;</td>
        <td width="15%" colspan="2" align="center" class="TITLE_SIM3" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;"><?php echo $client_city.", ".$tgl_now."<br>".$_SESSION["nama_usr"]; ?></td>
    </tr>
    <tr>    
        <td width="50%" colspan="6" align="left" class="TITLE_SIM3">&nbsp;</td>
    </tr>
    <tr>    
        <td width="50%" colspan="6" align="left" class="TITLE_SIM3">&nbsp;</td>
    </tr>
    <tr>
        <td width="35%" colspan="4" align="center" class="TITLE_SIM3">&nbsp;</td>
        <td width="15%" colspan="2" align="center" class="TITLE_SIM3" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;"><? echo ".........................."; ?></td>
    </tr>
    <tr>   
        <td width="50%" colspan="6" align="left" class="TITLE_SIM3">&nbsp;</td>
    </tr>
    <tr>
        <td width="50%" colspan="6" align="left" class="TITLE_SIM3" style="font-family: Tahoma; font-size: 11px; letter-spacing: 2px;">** Terima Kasih ** <br /> Dokumen dicetak komputer, tidak perlu stempel</td>
    </tr>
</table>
<!--
<table border="0" align="left" width="50%">
    <tr>    
        <td align="left" class="TITLE_SIM3">&nbsp;</td>
    </tr>
    <tr>
        <td align="left" class="TITLE_SIM3" style="font-family: Tahoma; font-size: 10px; letter-spacing: 2px;">** Terima Kasih ** <br /> Dokumen dicetak komputer, tidak perlu stempel</td>
    </tr>
    <tr>
        <td align="right" class="TITLE_SIM3" style="font-family: Tahoma; font-size: 10px; letter-spacing: 2px;"><u><? echo $_SESSION["nama_usr"]; ?></u></td>
    </tr>
</table>
-->
<SCRIPT LANGUAGE="JavaScript">
    printWindow();
</script>

</body>
</html>
<?php
function tanggal($tanggal) {
        $arrTanggal = explode('-', $tanggal);
        $hari = $arrTanggal[2];
        $bulan = $arrTanggal[1];
        $tahun = $arrTanggal[0];
        $result = $hari . ' ' . bulan($bulan) . ' ' . $tahun;
        return $result;
    }
function bulan($params) {
    switch ($params) {
        case 1:
            $bln = "Jan";
            break;
        case 2:
            $bln = "Peb";
            break;
        case 3:
            $bln = "Mar";
            break;
        case 4:
            $bln = "Apr";
            break;
        case 5:
            $bln = "Mei";
            break;
        case 6:
            $bln = "Jun";
            break;
        case 7:
            $bln = "Jul";
            break;
        case 8:
            $bln = "Agu";
            break;
        case 9:
            $bln = "Sep";
            break;
        case 10:
            $bln = "Okt";
            break;
        case 11:
            $bln = "Nop";
            break;
        case 12:
            $bln = "Des";
            break;
            break;
    }
    return $bln;
}
?>
