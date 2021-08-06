<?php

namespace PolombardamModels;

class GoodStudioPhoto extends ModelBase {

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var string
     */
    public $good_name;

    /**
     *
     * @var string
     */
    public $file_name;

    /**
     *
     * @var string
     */
    public $extension;

    /**
     *
     * @return string
     */
    public static function getTemporaryUploadImagePath() {
        return APP_PATH . '/public/studio/_unsorted/';
    }

    public function initialize() {
        $this->setSource('good_studio_photo');
    }

    /**
     *
     * @return string|null
     */
    public function getImage() {
        $path = '/studio/' . $this->file_name;

        if (is_file(APP_PATH . '/public' . $path)) {
            return $path;
        }

        return null;
    }

    /**
     *
     * @return string
     */
    public function getImageName() {
        return $this->file_name;
    }

    /**
     *
     * @return string|null
     */
    public function getImagePreview() {
        $file = explode('.', $this->file_name);

        $path = '/studio/_preview/' . implode('.', $file) . '.jpg';

        if (is_file(APP_PATH . '/public' . $path)) {
            return $path;
        } else {
            // if no preview exist give original one
            return $this->getImage();
        }
    }

    /**
     *
     * @param string $good_name
     * @return GoodStudioPhoto|null
     */
    public static function findStudioPhoto(string $good_name): ?GoodStudioPhoto {
        $studio_photo = self::query()
                /*
                 * Такая конструкция необходимо чтобы по наименованию товара найти наиболее подходящее студийное фото.
                 * Просто ищется не фото по наименованию товара(которое может быть больше чем наименование студийного фото),
                 * а фото которое больше всего похоже по наименованию на товар.
                 *
                 * Например для товара "iPhone 4S 8Gb" может подойти одна из фото: "iPhone 4S 8Gb", "iPhone 4S", "iPhone 4"
                 */
                ->where(":name: LIKE CONCAT('%', good_name, '%')", ['name' => $good_name])
                // необходимо чтобы возвращалась не первая попавшаяся подходящая к товару фото, а максимально точное(полное) совпадение
                ->orderBy("LENGTH(good_name) DESC")
                ->limit(1)
                ->execute()
                ->getFirst();

        return ($studio_photo instanceof GoodStudioPhoto ? $studio_photo : null);
    }

}
