<?php

namespace App\Filament\Resources\FilamentProductResource\Pages;



use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\FilamentProductResource;
use App\Models\Product;
use App\Models\ProductVariation;
use Faker\Provider\Text;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use function Pest\Laravel\delete;

class FilamentProductVariationTypeOptions extends EditRecord
{
    protected static string $resource = FilamentProductResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make('delete'),
        ];
    }

    public function form(Form $form): Form
    {
        $variationType = $this->record->variationType;
//        dd($variationType);
        $fields = [];
        foreach ($variationType as $variation) {
            $fields[] = TextInput::make('variation_type_'. $variation->id . '.id')->hidden();
            $fields[] = TextInput::make('variation_type_'. $variation->id . '.name')
                ->label($variation->name)
                ->readOnly();
        }
        return $form
            ->schema([
                Repeater::make('productVariation')
                    ->label(false)
                    ->collapsible()
                    ->defaultItems(1)
                    ->addable(false)
                    ->deletable(false)
                    ->schema([
                        Section::make()
                            ->schema($fields)
                            ->columns(count($variationType) < 4 ? count($variationType): 3),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->minValue(1)
                            ->numeric(),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->columnSpan(2)
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $existingProductVariation = $this->record->productVariation->toArray();
        $variationType = $this->record->variationType;

        $data['productVariation'] = $this->mergeCartesianWithExisting($variationType, $existingProductVariation);

//        dd($data);
        return $data;
    }

    protected function mergeCartesianWithExisting($variationType, $existingProductVariation): array
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;
        $cartesianItems = $this->cartesianItems($variationType, $defaultPrice, $defaultQuantity);
        $mergedCartesianItems = [];

//        dd($cartesianItems);
        if(!empty($cartesianItems)) {
            foreach ($cartesianItems as $cartesianItem) {

                $option_ids = collect($cartesianItem)
                    ->filter(fn ($_, $key) => str_starts_with($key, 'variation_type_'))
                    ->map(fn($item)=> $item['id'])
                    ->values()
                    ->toArray();

//                dd($option_ids);


                $match = array_filter($existingProductVariation, function ($item) use ($option_ids) {
                    return json_decode($item['variation_type_option_ids']) === $option_ids;
                });

//                dd($match);

                if(!empty($match)){
                    $existing = reset($match);
                    $cartesianItem['id'] = $existing['id'];
                    $cartesianItem['quantity'] = $existing['quantity'];
                    $cartesianItem['price'] = $existing['price'];
                } else {
                    $cartesianItem['quantity'] = $defaultQuantity;
                    $cartesianItem['price'] = $defaultPrice;
                }

                $mergedCartesianItems[] = $cartesianItem;
            }

        }

        return $mergedCartesianItems;
    }

    protected function cartesianItems($variationType, $defaultPrice, $defaultQuantity): array
    {
        $cartesianItems = [[]];

//        dd($variationType, $defaultPrice, $defaultQuantity);

        if(count($variationType) > 0){

            foreach ($variationType as $type){
                $temp = [];
                foreach ($type->variationTypeOption as $option) {
                    foreach ($cartesianItems as $cartesianItem){
                        $newCombination = $cartesianItem + [
                                'variation_type_'.$type->id =>[
                                    'id'=>$option->id,
                                    'name'=>$option->name,
                                    'variation_type_name'=>$type->name,
                                ]
                            ];
                        $temp[] = $newCombination;
                    }
                }
                $cartesianItems = $temp;
            }

            foreach ($cartesianItems as $key => &$cartesianItem) {
                if(count($cartesianItem) === count($variationType)){
                    $cartesianItem['price'] = $defaultPrice;
                    $cartesianItem['quantity'] = $defaultQuantity;
                }
            }

          return $cartesianItems;
        }
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $formattedData = [];

        foreach ($data['productVariation'] as $key => $value) {
            $variation_type_option_ids = collect($value)
                ->filter(fn ($_, $key) => str_starts_with($key, 'variation_type_'))
                ->map(fn($item)=> $item['id'])
                ->values()
                ->toArray();

            $formattedData[] = [
                'product_id'=> $this->record->id,
                'variation_type_option_ids'=>$variation_type_option_ids,
                'quantity'=>$value['quantity'],
                'price'=>$value['price'],
            ];
        }
        $data['productVariation'] = $formattedData;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $productVariations = $data['productVariation'];

        unset($data['productVariation']);

        $existing_product_variations = $this->record->productVariation->toArray() ?: [];

        $product_variation_id_count = count(reset($productVariations)['variation_type_option_ids']);

        $existing_product_variation_id_count = !empty($existing_product_variations) ? count(json_decode(reset($existing_product_variations)['variation_type_option_ids'])) : 0;

        $split_product_variations = $this->split_product_variations($productVariations, $existing_product_variations, $product_variation_id_count, $existing_product_variation_id_count);

            $new_product_variations = $split_product_variations['new_product_variations'];

            if(!empty($new_product_variations)){
                foreach ($new_product_variations as $productVariation) {
                    ProductVariation::create([
                        'product_id'=> $productVariation['product_id'],
                        'variation_type_option_ids'=>json_encode($productVariation['variation_type_option_ids']),
                        'quantity'=>$productVariation['quantity'],
                        'price'=>$productVariation['price'],
                    ]);
                }
            }

            $updated_existing_product_variations = $split_product_variations['updated_product_variations'];

            if(!empty($updated_existing_product_variations)){
            $productVariations = collect($updated_existing_product_variations)->map(function ($variation) {
                return [
                    'id'=>$variation['id'],
                    'variation_type_option_ids'=>json_encode($variation['variation_type_option_ids']),
                    'quantity'=>$variation['quantity'],
                    'price'=>$variation['price'],
                ];
            })->toArray();

            if(!empty($productVariations)){
                $record->productVariation()->upsert($productVariations,['id'], ['variation_type_option_ids', 'quantity', 'price']);
            }
        }

        return $record;

    }

    protected function split_product_variations($productVariations, $existing_product_variations, $product_variation_id_count, $existing_product_variation_id_count): array
    {
        $new_product_variations = [];
        $updated_product_variations = [];

        $variation_id_difference = $product_variation_id_count - $existing_product_variation_id_count;

        // Loop through each form variation
        foreach ($productVariations as $product_variation) {
            $is_new = true;

            // Check if the current form variation already exists in the database
            foreach ($existing_product_variations as $existing_product_variation) {
                $variation_type_option_ids = json_decode($existing_product_variation['variation_type_option_ids']);

                $existing_option_ids_slice = $variation_type_option_ids;
                $product_option_ids_slice = $product_variation['variation_type_option_ids'];
                // Slicing logic based on the variation_id_difference
                if ($variation_id_difference > 0) {
                    // Slice the current form variation to match the existing variation's length
                    $product_option_ids_slice = array_slice($product_variation['variation_type_option_ids'], 0, $existing_product_variation_id_count);
                } else if ($variation_id_difference < 0) {
                    // Slice the existing variation to match the current form variation's length
                    $existing_option_ids_slice = array_slice($variation_type_option_ids, 0, $product_variation_id_count);
                }

                // Compare the sliced arrays
                if ($product_option_ids_slice == $existing_option_ids_slice) {
                    // Found a match, mark it as updated
                    $is_new = false;

                    // Merge the product variation data with the existing database data
                    $updated_product_variation = $product_variation;
                    $updated_product_variation['id'] = $existing_product_variation['id']; // Preserve ID from DB
                    $updated_product_variations[] = $updated_product_variation;
                    break; // No need to check further, as we found a match
                }
            }

            // If it's determined to be a new variation, add it to new variations
            if ($is_new) {
                $new_product_variations[] = $product_variation;
            }
        }

        return [
            'new_product_variations' => $new_product_variations,
            'updated_product_variations' => $updated_product_variations
        ];
    }


}
