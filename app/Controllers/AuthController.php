<?php
namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function index()
    {
        // Jika sudah login, lempar ke dashboard masing-masing
        if (session()->get('logged_in')) {
            return session()->get('role') == 'admin' ? redirect()->to('/admin/dashboard') : redirect()->to('/produksi/dashboard');
        }
        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $model = new UserModel();
        
        $username = $this->request->getVar('username');
        $inputPassword = $this->request->getVar('password');

        $user = $model->getUserByUsername($username);

        if ($user) {
            $isPasswordValid = false;

            // 1. Cek jika menggunakan password_verify (untuk akun baru dari menu Pengaturan)
            if (password_verify($inputPassword, $user['password'])) {
                $isPasswordValid = true;
            }
            // 2. Fallback cek jika menggunakan md5 (untuk akun lama/bawaan)
            elseif (md5($inputPassword) === $user['password']) {
                $isPasswordValid = true;
                
                // (Opsional bagus) Otomatis upgrade password lama ke standard password_verify
                // agar ke depannya konsisten menggunakan keamanan modern
                // $newHash = password_hash($inputPassword, PASSWORD_DEFAULT);
                // $model->update($user['id'], ['password' => $newHash]);
            }

            if ($isPasswordValid) {
                $sesData = [
                    'id'         => $user['id'],
                    'username'   => $user['username'],
                    'full_name'  => $user['full_name'],
                    'role'       => $user['role'],
                    'logged_in'  => TRUE
                ];
                $session->set($sesData);

                if ($user['role'] == 'admin') {
                    return redirect()->to('/admin/dashboard');
                } else {
                    return redirect()->to('/produksi/dashboard');
                }
            } else {
                $session->setFlashdata('msg', 'Password salah!');
                return redirect()->to('/auth');
            }
        } else {
            $session->setFlashdata('msg', 'Username tidak ditemukan!');
            return redirect()->to('/auth');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth');
    }
}