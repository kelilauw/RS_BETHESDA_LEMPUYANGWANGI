<?php
if($_GET[p]=="335" or $_GET[p]=="cetak.rincian"){
	$tindakan = getFromTable("  select sum(a.tagihan) AS tagihan, sum(a.dibayar_penjamin) AS dibayar_penjamin, sum(b.jasa_dokter*a.qty) AS jasa_dokter, sum(b.jasa_asisten*a.qty) AS jasa_asisten, 
					       sum(jasa_rs*a.qty) AS jasa_rs, sum(alat*a.qty) AS alat, sum(bahan*a.qty) AS bahan, sum(dll*a.qty) AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%TINDAK%'
								");
								
	
	$tindakan_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%TINDAK%'
								"));

	$tindakanPenjamin = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%TINDAK%'
								");

	$visite   = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%VISIT%'
								");
	$visitePenjamin   = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%VISIT%'
								");

	$visite_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%VISIT%'
								"));

	$layananDokter   = getFromTable("select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%PEMERIKSAAN DOK%'
								");

	$layananDokterPenjamin   = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%PEMERIKSAAN DOK%'
								");

	$layananDokter_jasa   = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%PEMERIKSAAN DOK%'
								"));

	$potongan = getFromTable("SELECT SUM(jumlah) FROM rs00005 WHERE kasir = 'POT' AND reg='".$_GET['rg']."'");

	$konsul   = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%KONSUL%'
								");

	$konsulPenjamin  = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%KONSUL%'
								");

	$konsul_jasa   = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%KONSUL%'
								"));

	$alat     = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%ALAT%'
								");

	$alatPenjamin     = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%ALAT%'
								");
	$alat_jasa   = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%ALAT%'
								"));
	$laborat  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%LABO%'
								");
	$laboratPenjamin  = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%LABO%'
								");
	
	$laborat_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%LABO%'
								"));
	
	$radiologi= getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%RADIO%'
								");
	$radiologiPenjamin= getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%RADIO%'
								");

	$radiologi_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%RADIO%'
								"));

	$usg	  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%USG%'
								");

	$usgPenjamin	  = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%USG%'
								");

	$usg_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%USG%'
								"));

	$oksigen  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%OKSI%'
								");

	$oksigenPenjamin  = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%OKSI%'
								");

	$fisio    = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%FISIO%'
								");

	$fisioPenjamin    = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%FISIO%'
								");

	$fisio_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%FISIO%'
								"));

	$ambulan  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%AMBUL%'
								");

	$ambulanPenjamin  = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%AMBUL%'
								");

	$admin    = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%PENDAFTARAN%'
								");
								
	$admin_jasa = pg_fetch_array(pg_query("SELECT b.jasa_dokter*a.qty AS jasa_dokter, b.jasa_asisten*a.qty AS jasa_asisten, 
					       jasa_rs*a.qty AS jasa_rs, alat*a.qty AS alat, bahan*a.qty AS bahan, dll*a.qty AS dll
								from rs00008 a
								join rs00034 b on b.id=a.item_id::numeric
								join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%PENDAFTARAN%'
								"));

	$adminPenjamin    = getFromTable("  select sum(a.dibayar_penjamin) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tdesc like '%PENDAFTARAN%'
								");

        $obat = getFromTable("  select sum(tagihan) as jumlah 
								from rs00008   
								where no_reg='".$_GET['rg']."' AND (trans_type = 'OB1' OR trans_type = 'RCK')
								");
        $obatPenjamin = getFromTable("  select sum(dibayar_penjamin) as jumlah 
								from rs00008   
								where no_reg='".$_GET['rg']."' AND (trans_type = 'OB1' OR trans_type = 'RCK')
								");
					
        $obatReturn = getFromTable("  select sum(tagihan) as jumlah
								from rs00008_return   
								where no_reg='".$_GET['rg']."' AND (trans_type = 'OB1' OR trans_type = 'RCK')
								");
        $obatPenjaminReturn = getFromTable("  select sum(dibayar_penjamin) as jumlah 
								from rs00008_return   
								where no_reg='".$_GET['rg']."' AND (trans_type = 'OB1' OR trans_type = 'RCK')
								");
	$bhp = getFromTable("select sum(tagihan) as jumlah ".
						 "from rs00008 where no_reg='".$_GET['rg']."' AND trans_type = 'BHP' ");
					
	$bhpPenjamin = getFromTable("select sum(dibayar_penjamin) as jumlah ".
						 "from rs00008 where no_reg='".$_GET['rg']."' AND trans_type = 'BHP' ");
					
					
	$paket = getFromTable("select sum(jumlah) as jumlah ".
						 "from rs00005 where reg='".$_GET['rg']."' AND is_karcis='N' AND is_obat='N' AND kasir in ('IGD','RJN','RJL','RIN') ".
						 "AND layanan in ('888') ");
						 
	
	$akomodasi = getFromTable("select sum(tagihan) as jumlah " .
						    "from rs00008 where no_reg='" . $_GET['rg'] . "' AND trans_type = 'POS' AND trans_form = '370' AND qty > 0  ");
					 

	$akomodasiPenjamin = getFromTable("select sum(dibayar_penjamin) as jumlah " .
						    "from rs00008 where no_reg='" . $_GET['rg'] . "' AND trans_type = 'POS' AND trans_form = '370' AND qty > 0  ");



	$lain  = getFromTable("select sum(a.tagihan) as jumlah 
					from rs00008 a
					left join rs00034 b on b.id=a.item_id::numeric
					left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
					where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tc = '012'
					");

	$lainPenjamin  = getFromTable("select sum(a.dibayar_penjamin) as jumlah 
					from rs00008 a
					left join rs00034 b on b.id=a.item_id::numeric
					left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
					where a.no_reg='".$_GET['rg']."' AND (a.trans_type='LTM') and c.tc = '012'
					");


	$total = $admin + $tindakan+ $visite + $layananDokter + $konsul + $alat + $bhp + $obat + $laborat + $radiologi + $usg + $oksigen + $fisio + $ambulan + $akomodasi + $lain;
	$totalPenjamin = $adminPenjamin + $tindakanPenjamin + $visitePenjamin + $layananDokterPenjamin + $konsulPenjamin + $alatPenjamin + $bhpPenjamin + $obatPenjamin + $laboratPenjamin + $radiologiPenjamin + $usgPenjamin + $oksigenPenjamin + $fisioPenjamin + $ambulanPenjamin + $akomodasiPenjamin + $lainPenjamin;

}elseif($_GET[p]=="lap_pend_rj"){
    $tindakan = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%TINDAK%'
								");
	$visite   = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%VISIT%'
								");
	$konsul   = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%KONSUL%'
								");
	$alat     = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%ALAT%'
								");
	$laborat  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%LABO%'
								");
	$radiologi= getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%RADIO%'
								");
	$usg	  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%USG%'
								");
	$oksigen  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%OKSI%'
								");
	$fisio    = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%FISIO%'
								");
	$ambulan  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%AMBUL%'
								");
	$admin    = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%ADMIN%'
								");

	$obat = getFromTable("select sum(jumlah) as jumlah " .
					"from rs00005 where reg='" . $row1["reg"] . "' AND is_karcis='N' AND is_obat='Y' AND kasir in ('IGD','RJN','RJL','RIN') " .
					"AND layanan in ('99997', '99995', '320RJ_SWD','320RJ_IGD') ");
					
	$bhp = getFromTable("select sum(jumlah) as jumlah ".
						 "from rs00005 where reg='".$row1["reg"]."' AND is_karcis='N' AND is_obat='N' AND kasir in ('IGD','RJN','RJL','RIN') ".
						 "AND layanan in ('333') ");
					
	$paket = getFromTable("select sum(jumlah) as jumlah ".
						 "from rs00005 where reg='".$row1["reg"]."' AND is_karcis='N' AND is_obat='N' AND kasir in ('IGD','RJN','RJL','RIN') ".
						 "AND layanan in ('888') ");
						 
	$lain  = getFromTable("  select sum(a.tagihan) as jumlah 
								from rs00008 a
								left join rs00034 b on b.id=a.item_id::numeric
								left join rs00001 c on c.tc = b.sumber_pendapatan_id and c.tt='SBP'  
								where a.no_reg='".$row1["reg"]."' AND (a.trans_type='LTM') and c.tdesc like '%lain%'
								");


	//$total = $lain + $akomodasi + $karcis + $tindakan + $visite + $konsul + $alat + $laborat + $radiologi + $usg + $oksigen + $fisio + $ambulan + $admin + ($obat-$obatReturn) + $bhp + $paket;

	
}
?>