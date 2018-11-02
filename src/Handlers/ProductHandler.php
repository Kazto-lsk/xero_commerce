<?php

namespace Xpressengine\Plugins\XeroCommerce\Handlers;

use App\Facades\XeMedia;
use App\Facades\XeStorage;
use Xpressengine\Category\Models\Category;
use Xpressengine\Category\Models\CategoryItem;
use Xpressengine\Http\Request;
use Xpressengine\Media\Models\Image;
use Xpressengine\Plugins\XeroCommerce\Events\NewProductRegisterEvent;
use Xpressengine\Plugins\XeroCommerce\Models\Label;
use Xpressengine\Plugins\XeroCommerce\Models\Product;
use Xpressengine\Plugins\XeroCommerce\Models\ProductCategory;
use Xpressengine\Plugins\XeroCommerce\Models\ProductLabel;
use Xpressengine\Plugins\XeroCommerce\Models\ProductRevision;

class ProductHandler
{
    /**
     * @param  integer $productId productId
     *
     * @return Product
     */
    public function getProduct($productId)
    {
        $item = Product::where('id', $productId)->first();

        return $item;
    }

    /**
     * @param Request $request request
     *
     * @return Product
     */
    public function getProductsQueryForSetting(Request $request)
    {
        $query = new Product();

        $query = $this->settingMakeWhere($request, $query);
        $query = $this->commonMakeWhere($request, $query);

        return $query;
    }

    public function getProductsQueryForModule(Request $request, $config)
    {
        $query = new Product();

        $query = $this->moduleMakeWhere($request, $query, $config);
        $query = $this->commonMakeWhere($request, $query);

        return $query;
    }

    private function moduleMakeWhere(Request $request, $query, $config)
    {
        $targetProductIds = [];
        if ($categoryItemId = $config->get('categoryItemId')) {
            $categoryItem = CategoryItem::where('id', $categoryItemId)->first();

            $categoryIds[] = $categoryItem->id;
            foreach ($categoryItem->descendants as $desc) {
                $categoryIds[] = $desc->id;
            }

            $productIds = ProductCategory::whereIn('category_id', $categoryIds)->pluck('product_id')->toArray();

            $targetProductIds = array_merge($targetProductIds, $productIds);
        }

        if ($labels = $config->get('labels')) {
            $productIds = ProductLabel::whereIn('label_id', $labels)->pluck('product_id')->toArray();

            $targetProductIds = array_intersect($targetProductIds, $productIds);
        }

        $targetProductIds = array_unique($targetProductIds);
        $query = $query->whereIn('id', $targetProductIds);

        return $query;
    }

    /**
     * @param Request $request request
     * @param Product $query   product
     *
     * @return Product
     */
    private function settingMakeWhere(Request $request, $query)
    {
        return $query;
    }

    /**
     * @param Request $request request
     * @param Product $query   product
     *
     * @return Product
     */
    private function commonMakeWhere(Request $request, $query)
    {
        $args = $request->all();

        if (isset($args['product_name']) == true) {
            $query = $query->where('name', 'like', '%' . $args['product_name'] . '%');
        }

        if (isset($args['product_code']) == true) {
            $query = $query->where('product_code', 'like', '%' . $args['product_code'] . '%');
        }

        if (isset($args['product_deal_state'])) {
            $query = $query->where('state_deal', $args['product_deal_state']);
        }

        if (isset($args['product_display_state'])) {
            $query = $query->where('state_display', $args['product_display_state']);
        }

        if (isset($args['product_tax_type']) == true) {
            $query = $query->where('tax_type', $args['product_tax_type']);
        }

        return $query;
    }

    /**
     * @param  array $args productArgs
     *
     * @return integer
     */
    public function store(array $args)
    {
        $newProduct = new Product();

        $newProduct->fill($args);
        $info = array_combine($args['infoKeys'], $args['infoValues']);
        $newProduct->detail_info = json_encode($info);

        $newProduct->save();

        $this->storeRevision($newProduct);

        foreach ($args['images'] as $image) {
            if ($image != null) {
                $this->saveImage($image, $newProduct);
            }
        }

        \Event::dispatch(new NewProductRegisterEvent($newProduct));

        return $newProduct->id;
    }

    public function saveImage($imageParm, Product $newProduct)
    {
        $file = XeStorage::upload($imageParm, 'public/xero_commerce/product');
        $imageFile = XeMedia::make($file);
        XeMedia::createThumbnails($imageFile, 'widen', config('xe.media.thumbnail.dimensions'));
        $newProduct->images()->attach($imageFile->id);
        return $imageFile;
    }

    public function update(Product $product, $args)
    {
        $attributes = $product->getAttributes();
        foreach ($args as $name => $value) {
            if (array_key_exists($name, $attributes) === true) {
                $product->{$name} = $value;
            }
        }
        $info = array_combine(key_exists('infoKeys', $args) ? $args['infoKeys'] : [], key_exists('infoValues', $args) ? $args['infoValues'] : []);
        $product->detail_info = json_encode($info);
        $nonEditImage = key_exists('nonEditImage', $args) ? $args['nonEditImage'] : [];
        $editImages = $product->images()->whereNotIn('files.id', $nonEditImage)->get();
        $editImages->each(function (Image $originImage, $key) use ($args, $product) {
            if (count($args['editImages']) > 0) {
                if ($args['editImages'][$key] != null) {
                    $editImage = $this->saveImage($args['editImages'][$key], $product);
                    $product->images()->updateExistingPivot($originImage->id, ['image_id' => $editImage->id]);
                }
            } else {
                $originImage->delete();
            }
        });

        foreach ($args['addImages'] as $image) {
            if ($image != null) {
                $this->saveImage($image, $product);
            }
        }

        $product->save();

        $this->storeRevision($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        $this->storeRevision($product);
    }

    private function storeRevision($product)
    {
        $revisionNo = 0;
        $lastRevision = ProductRevision::where('id', $product->id)->max('revision_no');
        if ($lastRevision !== null) {
            $revisionNo = $lastRevision + 1;
        }

        $revisionProduct = new ProductRevision();

        $revisionProduct->fill($product->getAttributes());

        $revisionProduct->id = $product->id;
        $revisionProduct->revision_no = $revisionNo;
        $revisionProduct->detail_info = $product->detail_info;
        $revisionProduct->origin_deleted_at = $product->deleted_at;
        $revisionProduct->origin_created_at = $product->created_at;
        $revisionProduct->origin_updated_at = $product->updated_at;

        $revisionProduct->save();
    }
}
