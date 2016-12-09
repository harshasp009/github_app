<?php

  require_once 'vendor/autoload.php';
  require 'PHPExcel/Classes/PHPExcel.php';
  require 'PHPExcel/Classes/PHPExcel/IOFactory.php';
  ini_set('memory_limit','-1');
  ini_set('max_execution_time', -1);
  use Milo\Github;

  header('Content-Type: application/json');

  $api = new Github\Api;
  $token = new Milo\Github\OAuth\Token('14d80fa62809e44bbfa8a371bf058d58269ae869');
  $api->setToken($token);

  $objPHPExcel = new PHPExcel();
  $objPHPExcel->setActiveSheetIndex(0);

  if(isset($_POST['submit'])) {
    $github_repo = $_POST['github_repo'];
    $repo_url = parse_url($github_repo);
    $github = $repo_url['path'];
    $repositorySearchResponse= $api->get($github);
    $repositorySearchData= $api->decode($repositorySearchResponse);

    $rowCount = 2;
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Name');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Public Email');
    $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'URL');
    $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Company');
    $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Location');
    $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Primary Github Email');
    $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Repositories');
    $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Organization');
    //$output = "<table><thead><tr><td>Name</td><td>Public email</td><td>URL</td><td>Company</td><td>Location</td><td>Primary Github email</td><td>Additional email addresses</td><td>Repositories</td><td>Organization</td></tr></thead><tbody>";
    foreach($repositorySearchData as $data) {
      $url =  $data->stargazers_url;
      $p=parse_url($url);
      $stargazers_url = $p['path'];
      $user_url = $api->get($stargazers_url);
      $userinfo = $api->decode($user_url);
      foreach($userinfo as $info) {
        $user_info_data = $info->url;
        $parse = parse_url ($user_info_data);
        $urls = $parse['path'];
        $user_urls = $api->get ($urls);
        $userinfos = $api->decode ($user_urls);

        $objPHPExcel->getActiveSheet()
          ->SetCellValue('A' . $rowCount, $userinfos->name);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('B' . $rowCount, $userinfos->email);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('C' . $rowCount, $userinfos->url);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('D' . $rowCount, $userinfos->company);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('E' . $rowCount, $userinfos->location);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('F' . $rowCount, $userinfos->email);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('G' . $rowCount, $userinfos->repos_url);
        $objPHPExcel->getActiveSheet()
          ->SetCellValue('H' . $rowCount, $userinfos->organizations_url);


//        $output .= "<tr><td>".$userinfos->name."</td>";
//          $output .= "<td>".$userinfos->email."</td>";
//          $output .= "<td>".$userinfos->url."</td>";
//          $output .= "<td>".$userinfos->company."</td>";
//          $output .= "<td>".$userinfos->location."</td>";
//          $output .= "<td>".$userinfos->email."</td>";
//          $output .= "<td>".$userinfos->repos_url."</td>";
//          $output .= "<td>".$userinfos->organizations_url."</td></tr>";

         $rowCount++;
      }
    }
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=\"results.xlsx\"");
    header("Cache-Control: max-age=0");
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save("php://output");
    exit();
    //echo $output .= "</tbody></table>";

  }



?>