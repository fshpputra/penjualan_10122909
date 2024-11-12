<?php
defined('BASEPATH') or exit('No direct script access allowed');
class member extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('m_member', 'm_user'));
        date_default_timezone_set('Asia/Jakarta');
    }
    public function dataMember()
    {
        if (!$this->session->userdata('level') == 'Admin') {
            redirect('login');
        } else {
            $data['admin'] = $this->m_user->selectAdmin()->row();
            $data['member'] = $this->m_member->getMember()->result();
            $this->load->view('admin/header', $data);
            $this->load->view('admin/dataMember');
            $this->load->view('admin/footer');
        }
    }

    public function import()
    {
        // Ambil data admin
        $data['admin'] = $this->m_user->selectAdmin()->row();

        // Periksa level pengguna, jika bukan Admin, arahkan ke halaman login
        if ($this->session->userdata('level') != 'Admin') {
            redirect('login');
        } else {
            if (isset($_POST['preview'])) { // Jika user menekan tombol Preview pada form
                // Lakukan upload file dengan memanggil fungsi upload yang ada di m_member
                $upload = $this->m_member->upload_file($this->filename);

                if ($upload['result'] == "success") { // Jika proses upload sukses
                    // Load plugin PHPExcel
                    $excelreader = new PHPExcel_Reader_Excel2007();
                    $loadexcel = $excelreader->load('excel/' . $this->filename . '.xlsx'); // Load file yang diupload ke folder excel
                    $sheet = $loadexcel->getActiveSheet()->toArray(null, true, true, true);

                    // Masukan variabel $sheet ke dalam array data untuk dikirim ke view form.php
                    $data['sheet'] = $sheet;
                } else { // Jika proses upload gagal
                    $data['upload_error'] = $upload['error']; // Ambil pesan error upload untuk ditampilkan di form
                }
            }

            // Ambil data member
            $data['member'] = $this->m_member->getMember()->result();

            // Load view dengan data yang sudah dipersiapkan
            $this->load->view('admin/header', $data);
            $this->load->view('admin/tambahmember');
            $this->load->view('admin/footer');
        }
    }

    public function tambah()
    {
        // Load file yang telah diupload ke folder excel
        $excelreader = new PHPExcel_Reader_Excel2007();
        $loadexcel = $excelreader->load('excel/' . $this->filename . '.xlsx');
        $sheet = $loadexcel->getActiveSheet()->toArray(null, true, true, true);

        // Buat array untuk menampung data yang akan diinsert ke database
        $data = [];
        $numrow = 1;

        foreach ($sheet as $row) {
            // Lewati baris pertama yang berisi nama kolom
            if ($numrow > 1) {
                // Tambahkan data ke dalam array
                $data[] = [
                    'idMember' => "",             // Kosongkan kolom idMember
                    'nama'     => $row['B'],      // Data nama dari kolom B
                    'jk'       => $row['C'],      // Data jenis kelamin dari kolom C
                    'alamat'   => $row['D']       // Data alamat dari kolom D
                ];
            }
            $numrow++; // Tambah 1 setiap kali looping
        }

        // Insert data ke database melalui model
        $this->m_member->tambah($data);

        // Set flashdata untuk notifikasi
        $this->session->set_flashdata('info', 'Data berhasil ditambah');

        // Redirect ke halaman awal
        redirect("member/dataMember");
    }
}
