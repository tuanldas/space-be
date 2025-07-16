<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class InternationalizationTest extends TestCase
{
    public function test_english_translations_are_available(): void
    {
        // Set locale to English
        App::setLocale('en');

        // Test auth translations
        $this->assertEquals('Login successful', __('auth.login_success'));
        $this->assertEquals('Logout successful', __('auth.logout_success'));
        $this->assertEquals('Registration successful', __('auth.register_success'));
        $this->assertEquals('Token refreshed successfully', __('auth.refresh_token_success'));
        $this->assertEquals('These credentials do not match our records.', __('auth.failed'));
    }

    public function test_vietnamese_translations_are_available(): void
    {
        // Set locale to Vietnamese
        App::setLocale('vi');

        // Test auth translations
        $this->assertEquals('Đăng nhập thành công', __('auth.login_success'));
        $this->assertEquals('Đăng xuất thành công', __('auth.logout_success'));
        $this->assertEquals('Đăng ký thành công', __('auth.register_success'));
        $this->assertEquals('Làm mới token thành công', __('auth.refresh_token_success'));
        $this->assertEquals('Thông tin đăng nhập không chính xác.', __('auth.failed'));
    }

    public function test_english_pagination_translations_exist(): void
    {
        // Set locale to English
        App::setLocale('en');

        // Kiểm tra các khóa có tồn tại, không kiểm tra giá trị cụ thể
        $this->assertNotEquals('pagination.showing', __('pagination.showing'));
        $this->assertNotEquals('pagination.to', __('pagination.to'));
        $this->assertNotEquals('pagination.of', __('pagination.of'));
        $this->assertNotEquals('pagination.results', __('pagination.results'));
        $this->assertNotEquals('pagination.previous', __('pagination.previous'));
        $this->assertNotEquals('pagination.next', __('pagination.next'));
    }

    public function test_vietnamese_pagination_translations_exist(): void
    {
        // Set locale to Vietnamese
        App::setLocale('vi');

        // Kiểm tra các khóa có tồn tại, không kiểm tra giá trị cụ thể
        $this->assertNotEquals('pagination.showing', __('pagination.showing'));
        $this->assertNotEquals('pagination.to', __('pagination.to'));
        $this->assertNotEquals('pagination.of', __('pagination.of'));
        $this->assertNotEquals('pagination.results', __('pagination.results'));
        $this->assertNotEquals('pagination.previous', __('pagination.previous'));
        $this->assertNotEquals('pagination.next', __('pagination.next'));
    }

    public function test_english_password_translations_exist(): void
    {
        // Set locale to English
        App::setLocale('en');

        // Kiểm tra các khóa có tồn tại, không kiểm tra giá trị cụ thể
        $this->assertNotEquals('passwords.password', __('passwords.password'));
        $this->assertNotEquals('passwords.reset', __('passwords.reset'));
        $this->assertNotEquals('passwords.user', __('passwords.user'));
    }

    public function test_vietnamese_password_translations_exist(): void
    {
        // Set locale to Vietnamese
        App::setLocale('vi');

        // Kiểm tra các khóa có tồn tại, không kiểm tra giá trị cụ thể
        $this->assertNotEquals('passwords.password', __('passwords.password'));
        $this->assertNotEquals('passwords.reset', __('passwords.reset'));
        $this->assertNotEquals('passwords.user', __('passwords.user'));
    }
} 