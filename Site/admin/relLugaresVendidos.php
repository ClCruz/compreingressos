<?php
require_once('../settings/Utils.php');

// Host do Reporting Services
$report_host = "http://201.48.139.237:8081";
// URL do ReportViewer
$report_server = "/ReportServer/Pages/ReportViewer.aspx?";
// Pasta onde esta o relatório no ReportServer
$report_folder = "%2fRSCompreingressos";
// Nome do arquivo do relatório
$report_name = "%2f01-REL_LUGARES_VENDIDOS";
// URL completa para execução do relatório
$url_report = $report_host.$report_server.$report_folder.$report_name;
$params = array(
    1 => "PARAM_PECA",
    2 => "PARAM_SALA",
    3 => "PARAM_DATA_INI",
    4 => "PARAM_DATA_FIM",
    5 => "PARAM_CLIENTE",
    6 => "PARAM_CPF",
    7 => "PARAM_RG",
    8 => "rc:Parameters",
    9 => "rs:Command",
    10 => "PARAM_HR_INI",
    11 => "PARAM_HR_FIM"
);
foreach ($params as $key => $value) {
  if (!empty($_POST[$value])) {
    $param = $_POST[$value];
    if($key == 3 || $key == 4){
      $param = getDateF($param);
    }else if($key == 6 || $key == 7){
      $param = cleanDocuments($param);
    }else if($key == 2){
      $param = str_replace("TODOS", "-1", $param);
    }
    $url_report .= "&". $value ."=". $param;
  }
}
header("Location: ". $url_report);
?>
