<?php

namespace PolombardamModels;

/**
 * @todo Переделать Organization на что-то вроде MerchantNetworkConfig чем оно по сути и является
 */
class Organization extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $shortlink;

    /**
     *
     * @var string
     */
    public $merchant_name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     * JSON settings
     * @var string
     */
    public $settings;

    /**
     * Настройки организации/сети по умолчанию
     *
     * @var array
     */
    protected $default_settings = [
        'show_get_photo' => true,
        'show_get_answer' => true,
        'show_get_callback' => true
    ];

    public function initialize() {
        $this->setSource('organization');
    }

    /**
     *
     * @param int $organization_id
     * @return string
     */
    public static function getUrlByOrganizationId($organization_id) {
        $organization = Organization::findFirst((int) $organization_id);

        if ($organization) {
            return $organization->getUrl();
        } else {
            $organization = new Organization();
            $organization->id = $organization_id;

            return $organization->getUrl();
        }
    }

    /**
     *
     * @return string
     */
    public function getName() {
        return $this->merchant_name;
    }

    /**
     *
     * @return string
     */
    public function getUrl() {
        if ($this->shortlink) {
            return '/' . $this->shortlink;
        } else {
            return '/network/' . $this->id;
        }
    }

    /**
     *
     * @param int $organization_id
     * @param array $organization_data_array
     */
    public static function editFromApi($organization_id, $organization_data_array) {
        $errors = [];

        $organization = Organization::findFirst((int) $organization_id);

        if ($organization) {
            $org_exist = true;
        } else {
            $org_exist = false;

            $organization = new Organization();
            $organization->id = $organization_id;
        }

        if (isset($organization_data_array['data']['shortlink'])) {
            $organization->shortlink = $organization_data_array['data']['shortlink'];
        }
        if (isset($organization_data_array['data']['merchant_name'])) {
            $organization->merchant_name = $organization_data_array['data']['merchant_name'];
        }
        if (isset($organization_data_array['data']['description'])) {
            $organization->description = $organization_data_array['data']['description'];
        }
        if (isset($organization_data_array['data']['settings'])) {
            $organization->setSettings(array_merge($organization->getSettings(), (json_decode($organization_data_array['data']['settings'], true) ?: [])));
        }

        if ($org_exist) {
            if ($organization->save()) {
                $errors[] = [
                    true,
                    'organization-edit-save',
                    $organization_id
                ];
            } else {
                $errors[] = [
                    false,
                    'organization-edit-save',
                    $organization_id
                ];
            }
        } else {
            if ($organization->create()) {
                $errors[] = [
                    true,
                    'organization-edit-create',
                    $organization->id
                ];
            } else {
                $errors[] = [
                    false,
                    'organization-edit-create',
                    $organization->id
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return array_merge($this->default_settings, (json_decode($this->settings, true) ?: []));
    }

    /**
     * @param array $settings
     * @return Organization
     */
    public function setSettings(array $settings = []): Organization
    {
        $this->settings = json_encode($settings);

        return $this;
    }

}
