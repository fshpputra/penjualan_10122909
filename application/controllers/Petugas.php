<?php
defined('BASEPATH') or exit('No direct script access allowed');
class petugas extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_User');
        date_default_timezone_set('Asia/Jakarta');
    }
    
    public function index()
    {
        if ($this->session->userdata('level') != 'Petugas') {
            redirect('login');
        } else {
            $data['petugas'] = $this->m_user->selectPetugas()->row();
            $this->load->view('petugas/header');
            $this->load->view('petugas/home', $data);
            $this->load->view('petugas/footer');
        }
    }

    public function profil()
    {
        $data['petugas'] = $this->m_user->selectPetugas()->row();
        if ($this->session->userdata('level') != 'Petugas') {
            redirect('login');
        } else {
            if ($this->input->method() == 'post') {
                $this->m_user->ubahPetugas();
                $this->session->set_flashdata('info', 'Data berhasil diubah');
                redirect('petugas/profil');
            } else {
                $this->load->view('petugas/header');
                $this->load->view('petugas/profil', $data);
                $this->load->view('petugas/footer');
            }
        }
    }

    function export() {
        // Check if the user has Admin privileges
        if ($this->session->userdata('level') != 'Admin') {
            redirect('login');
        } else {
            $excel = new PHPExcel();
    
            // Setting the properties of the excel file
            $excel->getProperties()->setCreator('XYZ')
                ->setLastModifiedBy('XYZ')
                ->setTitle("Data Petugas")
                ->setSubject("Petugas")
                ->setDescription("Laporan Semua Data Petugas")
                ->setKeywords("Data Petugas");
    
            // Define style for the header
            $style_col = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'E1E0F7')
                ),
                'font' => array('bold' => true),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
    
            // Define style for table rows
            $style_row = array(
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
    
            // Set up header title
            $excel->setActiveSheetIndex(0)->setCellValue('A1', "DATA Petugas");
            $excel->getActiveSheet()->mergeCells('A1:E1');
            $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(TRUE);
            $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(15);
            $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    
            // Create table header
            $excel->setActiveSheetIndex(0)->setCellValue('A3', "NO");
            $excel->setActiveSheetIndex(0)->setCellValue('B3', "Id Petugas");
            $excel->setActiveSheetIndex(0)->setCellValue('C3', "Nama");
            $excel->setActiveSheetIndex(0)->setCellValue('D3', "Email");
    
            // Apply header styles
            $excel->getActiveSheet()->getStyle('A3')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('B3')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('C3')->applyFromArray($style_col);
            $excel->getActiveSheet()->getStyle('D3')->applyFromArray($style_col);
    
            // Retrieve data from model
            $dataPetugas = $this->m_user->getPetugas()->result();
            $no = 1; // Start numbering from 1
            $numrow = 4; // Start from row 4
    
            foreach ($dataPetugas as $data) {
                $excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $no);
                $excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $data->idUser);
                $excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $data->nama);
                $excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $data->email);
    
                // Apply row styles
                $excel->getActiveSheet()->getStyle('A' . $numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('B' . $numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('C' . $numrow)->applyFromArray($style_row);
                $excel->getActiveSheet()->getStyle('D' . $numrow)->applyFromArray($style_row);
    
                $no++;
                $numrow++;
            }
    
            // Set column widths
            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    
            // Set row height to auto
            $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
    
            // Set page orientation to landscape
            $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    
            // Set the sheet title
            $excel->getActiveSheet(0)->setTitle("Laporan Data Petugas");
            $excel->setActiveSheetIndex(0);
    
            // Output the Excel file
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Data Petugas.xlsx"');
            header('Cache-Control: max-age=0');
            $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $write->save('php://output');
        }
    }
    
    function exportPDF(){
        $data['dataPetugas'] = $this->m_user->getPetugas()->result();
        $this->pdf->load_view('admin/laporan/laporanPetugas',$data);
        $tgl = date("d/m/Y");
        $this->pdf->render();
        $this->pdf->stream("Laporan-Petugas_".$tgl.".pdf");
    }
}
