<? 
//  hery-- Sept 16, 2007 


$PID = "rm_bayi_edit";
$SC = $_SERVER["SCRIPT_NAME"];

require_once("startup.php");
     
   
if(isset($_GET["e"])) {
    $r = pg_query($con, "select * from rs00002 where mr_no = '".$_GET["e"]."'");
    $n = pg_num_rows($r);
    if($n > 0) $d = pg_fetch_object($r);
    pg_free_result($r);
    
    echo "<DIV ALIGN=RIGHT><A HREF='$SC?p=$PID&registered=Y&q=search&search=a'>".icon("back","Kembali")."</a></DIV>";
    
    title("Edit Identitas Pasien Bayi");
    
   		$f = new Form("actions/110b.update.php", "POST");
        $f->subtitle1("Identitas");
        $f->hidden("mr_no","$d->mr_no");
        $f->text("mr_no","No.MR",12,8,$d->mr_no,"DISABLED");
      
    $f->PgConn = $con;
    $f->text("f_nama","Nama Bayi",40,50,$d->nama);
    $f->selectArray("f_jenis_kelamin", "Jenis Kelamin",Array("L" => "Laki-laki", "P" => "Perempuan"),$d->jenis_kelamin);
    $f->text("f_tmp_lahir","Tempat Lahir",40,40,$d->tmp_lahir);
    $f->selectDate("f_tgl_lahir", "Tanggal Lahir", pgsql2phpdate($d->tgl_lahir));
    $f->text("f_umur", "(Umur)", 5,3,$d->umur,"disabled");
    
    $f->subtitle1("Identitas Orangtua");
    $f->text("f_nama_ayah","Nama Ayah ",50,50,$d->nama_ayah);
    $f->text("f_nama_ibu","Nama Ibu",50,50,$d->nama_ibu);
    $f->text("f_pekerjaan","Pekerjaan Orangtua ",50,50,$d->pekerjaan);
    $f->text("f_nama_keluarga","Nama Keluarga",40,50,$d->nama_keluarga);
    $f->selectSQL("f_agama_id", "Agama","select tc, tdesc from rs00001 where tt = 'AGM' and tc != '000'",$d->agama_id);
    $f->text("f_sukubangsa","Suku Bangsa",40,50,$d->sukubangsa);
    $f->text("f_pangkat_gol","Pangkat/Golongan ",50,50,$d->pangkat_gol);
	$f->text("f_jabatan","Jabatan",40,50,$d->jabatan);
	$f->text("f_nrp_nip","NRP/NIP ",50,50,$d->nrp_nip);
	$f->text("f_kesatuan","Kesatuan/Instansi/Pekerjaan ",50,50,$d->kesatuan);
	$f->selectSQL("f_gol_darah", "Golongan Darah","select '' as tc, '-' as tdesc union ".
        			  "select tc, tdesc from rs00001 where tt = 'GOL' and tc != '000'",$d->gol_darah);
    $f->selectSQL("f_resus_faktor", "Resus Faktor","select '' as tc, '-' as tdesc union ".
                        "select tc, tdesc from rs00001 where tt = 'REF' and tc != '000'",$d->resus_faktor);                                              
 	
	$f->subtitle1("Alamat Tetap");
	$f->text("f_alm_tetap","Alamat",50,50,$d->alm_tetap);
	$f->text("f_kota_tetap","Kota",50,50,$d->kota_tetap);
	$f->text("f_pos_tetap","Kode Pos",5,5,$d->pos_tetap);
	$f->text("f_tlp_tetap","Telepon",15,15,$d->tlp_tetap);
 
    $f->subtitle1("Keluarga Dekat");
    $f->text("f_keluarga_dekat","Nama",50,50,$d->keluarga_dekat);
    $f->text("f_alm_keluarga","Alamat",50,50,$d->alm_keluarga);
    $f->text("f_kota_keluarga","Kota",50,50,$d->kota_keluarga);
    $f->text("f_pos_keluarga","Kode Pos",5,5,$d->pos_keluarga);
    $f->text("f_tlp_keluarga","Telepon",15,15,$d->tlp_keluarga);   

    $f->hidden("f_alm_sementara",$d->alm_sementara);
    $f->hidden("f_kota_sementara",$d->kota_sementara);
    $f->hidden("f_pos_sementara",$d->pos_sementara);
    $f->hidden("f_tlp_sementara",$d->tlp_sementara);
	
    $f->selectSQL("f_tipe_pasien", "Tipe Pasien","select tc, tdesc from rs00001 where tt = 'JEP' and tc != '000'",
    			  "$d->tipe_pasien");                      
    $f->submit(" Simpan ");
    $f->execute();
}else {
   
	if (!$GLOBALS['print']){
    	title_print("<img src='icon/informasi-2.gif' align='absmiddle' > DATA PASIEN BAYI LAHIR");
    } else {
    	title("<img src='icon/informasi.gif' align='absmiddle' > DATA PASIEN BAYI LAHIR");
    }
    
    // search box
    //echo "<img src='icon/informasi-2.gif' align='absmiddle' >";
    //echo "<font class=FORM_TITLE>DATA PASIEN</font>";
    echo "<DIV ALIGN=RIGHT><TABLE BORDER=0><FORM ACTION=$SC><TR>";
    echo "<INPUT TYPE=HIDDEN NAME=p VALUE=$PID>";
    if(!$GLOBALS['print']){
    	echo "<TD class=FORM>Pencarian : <INPUT TYPE=TEXT NAME=search VALUE='".$_GET["search"]."'></TD>";
    	echo "<TD><INPUT TYPE=SUBMIT VALUE=' Cari '></TD>";
    }else{
    	echo "<TD class=FORM>Pencarian : <INPUT disabled TYPE=TEXT NAME=search VALUE='".$_GET["search"]."'></TD>";
    	echo "<TD><INPUT disabled TYPE=SUBMIT VALUE=' Cari '></TD>";
    }
    echo "</TR></FORM></TABLE></DIV>";

    $t = new PgTable($con, "100%");
  		$sql ="select a.mr_no,a.nama,a.nama_ibu,a.mr_no_ibu,a.nama_ayah,a.pangkat_gol,a.nrp_nip,a.kesatuan, ".
              "a.mr_no as href ".
              "FROM rs00002 a ".
              "where a.is_bayi='Y' ";

          if ($_GET["search"]){
          	$sql2 = " and upper(a.nama) LIKE '%".strtoupper($_GET["search"])."%' ".
		              "OR a.mr_no LIKE '%".$_GET["search"]."%' ";
		              //"OR upper(a.alm_tetap) like '%".strtoupper($_GET["search"])."%' ".
		              //"OR upper(a.kesatuan) like '%".strtoupper($_GET["search"])."%' ".
		              //"OR upper(a.pangkat_gol) like '%".strtoupper($_GET["search"])."%'" ;
          }
                            

        if (!isset($_GET[sort])) {
           $_GET[sort] = "mr_no";
           $_GET[order] = "asc";
		}
	$t->SQL = "$sql $sql2";
    $t->ColHeader = array("NO.MR","NAMA BAYI","NAMA IBU","NO.MR IBU","NAMA AYAH","PANGKAT","NRP/NIP","KESATUAN","EDIT");
    $t->ShowRowNumber = true;
    $t->ColAlign[0] = "CENTER";
    $t->ColAlign[3] = "CENTER";
    
    if(!$GLOBALS['print']){
		$t->RowsPerPage = 20;
    	$t->ColFormatHtml[8] = "<nobr><A CLASS=TBL_HREF HREF='$SC?p=$PID&e=<#8#>'>".icon("edit","Edit")."</A></nobr>";
    }else{
    	$t->RowsPerPage = 30;
    	$t->ColFormatHtml[8] = icon("edit","Edit");
    	$t->DisableNavButton = true;
    	$t->DisableScrollBar = true;
    	//$t->DisableStatusBar = true;
    }
    $t->execute();

}
?>