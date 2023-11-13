<?php

namespace App\Services\Dashboard;

use App\Models\{MasterRole, User, SettingWebsite};
use App\Models\Traits\Helpers\Websiteable;
use Illuminate\Support\Facades\{Validator};
use App\Services\Service;
use Illuminate\Http\Request;

class WebsiteService extends Service
{
  // ---------------------------------
  // TRAITS
  use Websiteable;


  // ---------------------------------
  // PROPERTIES
  protected array $rules = [
    "TEXT_WEB_TITLE" => ["required", "string", "max:30"],
    "TEXT_WEB_LOCATION" => ["required", "string"],
    "TEXT_HERO_HEADER" => ["required", "string", "max:125"],
    "TEXT_HERO_DESCRIPTION" => ["required", "string", "max:255"],
    "TEXT_FOOTER" => ["required", "string", "max:125"],
    "IMAGE_WEB_LOGO_WHITE" => ["nullable", "file", "image", "mimes:png,jpg", "max:5120"],
    "IMAGE_WEB_LOGO" => ["nullable", "file", "image", "mimes:png,jpg", "max:5120"],
    "IMAGE_WEB_FAVICON" => ["nullable", "file", "image", "mimes:png,jpg", "max:5120"],
    "IMAGE_FOOTER" => ["nullable", "file", "image", "mimes:png,jpg", "max:5120"],
    "IMAGE_FOOTER_DASHBOARD" => ["nullable", "file", "image", "mimes:png,jpg", "max:5120"],
  ];

  protected array $messages = [
    "TEXT_WEB_TITLE.required" => "Judul website tidak boleh kosong.",
    "TEXT_WEB_TITLE.string" => "Judul website harus berupa string.",
    "TEXT_WEB_TITLE.max" => "Judul website tidak boleh lebih dari :max karakter.",

    "TEXT_WEB_LOCATION.required" => "Lokasi website tidak boleh kosong.",
    "TEXT_WEB_LOCATION.string" => "Lokasi website harus berupa string.",

    "TEXT_HERO_HEADER.required" => "Header teks hero tidak boleh kosong.",
    "TEXT_HERO_HEADER.string" => "Header teks hero harus berupa string.",
    "TEXT_HERO_HEADER.max" => "Header teks hero tidak boleh lebih dari :max karakter.",

    "TEXT_HERO_DESCRIPTION.required" => "Deskripsi teks hero tidak boleh kosong.",
    "TEXT_HERO_DESCRIPTION.string" => "Deskripsi teks hero harus berupa string.",
    "TEXT_HERO_DESCRIPTION.max" => "Deskripsi teks hero tidak boleh lebih dari :max karakter.",

    "TEXT_FOOTER.required" => "Teks footer dashboard tidak boleh kosong.",
    "TEXT_FOOTER.string" => "Teks footer dashboard harus berupa string.",
    "TEXT_FOOTER.max" => "Teks footer dashboard tidak boleh lebih dari :max karakter.",

    "IMAGE_WEB_LOGO_WHITE.file" => "Logo website harus berupa file.",
    "IMAGE_WEB_LOGO_WHITE.image" => "Logo website harus berupa gambar.",
    "IMAGE_WEB_LOGO_WHITE.mimes" => "Logo website harus berupa file dengan format: :values.",
    "IMAGE_WEB_LOGO_WHITE.max" => "Logo website tidak boleh lebih dari :max KB.",

    "IMAGE_WEB_LOGO.file" => "Logo website harus berupa file.",
    "IMAGE_WEB_LOGO.image" => "Logo website harus berupa gambar.",
    "IMAGE_WEB_LOGO.mimes" => "Logo website harus berupa file dengan format: :values.",
    "IMAGE_WEB_LOGO.max" => "Logo website tidak boleh lebih dari :max KB.",

    "IMAGE_WEB_FAVICON.file" => "Favicon website harus berupa file.",
    "IMAGE_WEB_FAVICON.image" => "Favicon website harus berupa gambar.",
    "IMAGE_WEB_FAVICON.mimes" => "Favicon website harus berupa file dengan format: :values.",
  ];


  // ---------------------------------
  // CORES
  public function edit(MasterRole $userRole)
  {
    // Roles checking
    $roleName = $userRole->role_name;
    if ($roleName === "admin") return $this->adminEdit();

    // Redirect to unauthorized page
    return view("errors.403");
  }

  public function update(Request $request, User $user, MasterRole $userRole)
  {
    // Data processing
    $data = $request->all();

    // Roles checking
    $roleName = $userRole->role_name;
    if ($roleName === "admin") return $this->adminUpdate($data, $user);

    // Redirect to unauthorized page
    return view("errors.403");
  }


  // ---------------------------------
  // UTILITIES
  // ADMIN
  // Edit
  private function adminEdit()
  {
    $settings = SettingWebsite::all()->pluck("value", "key");

    // View variables
    $viewVariables = [
      "title" => "Pengaturan Website",
      "settings" => $settings,
    ];
    return view("pages.dashboard.actors.admin.website-settings.edit", $viewVariables);
  }
  // Update
  private function adminUpdate($data, User $user)
  {
    // Rules
    $rules = $this->settingWebsiteRules($this->rules, $data);
    // Validates
    $credentials = Validator::make($data, $rules, $this->messages)->validate();
    if (empty($credentials)) return redirect("/dashboard/website")
      ->withInfo("Kamu tidak melakukan editing pada website.");
    // Update
    $fields = $this->generateFields($credentials, $user);
    return $this->updateWebsite($fields);
  }
}
