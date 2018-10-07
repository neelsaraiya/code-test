<?php
/**
 * RaceController
 *
 * This controller is used to read xml files,json files and display horse names with price in ascending order
 *
 * @author     Neel Saraiya <neelsaraiya@gmail.com>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Flysystem\FileNotFoundException;


class RaceController extends Controller
{
    const WOLFERHAMPTON_FILE = 'storage/app/public/Wolferhampton_Race1.json';
    const CAULFIELD_FILE = 'storage/app/public/Caulfield_Race1.xml';


    /**
     * This function reads Caulfield_Race1.xml and display horsenames with price in ascending order
     */
    public function caulfield(){
        //Read json file and get content into an array format to parse through it
        try{
            $file = base_path(self::CAULFIELD_FILE);
            if(file_exists($file)){
                $fileContent = file_get_contents($file);
            }else{
                throw new FileNotFoundException($file);
            }
        } catch(FileNotFoundException $e){
            echo $e->getMessage();
            return;
        }

        $data = simplexml_load_string($fileContent);


        //create an array of horse names
        $horses = [];
        foreach($data->races->race->horses->horse as $row){
            $horses[strval($row->number)] = [
                'name'  =>  strval($row['name'])
            ];
        }

        //try and match the market selections based on horsenames and add price to horses array
        foreach($data->races->race->prices->price->horses->horse as $row){
            foreach ($horses as $horse_id=>$horse){
                if($horse_id == strval($row['number'])){
                    $horses[$horse_id]['price'] = strval($row['Price']);
                }
            }
        }

        //Sort Multi dimensial array based on horse price
        $horses = $this->sortByPrice($horses);

        return $horses;
    }

    /**
     * This function reads Wolferhampton_Race1.json and display horsenames with price in ascending order
     * This fuunction will throw an exception is file is not found at the location
     *
     * @return array sorted by price and horsenames
     * @throws FileNotFoundException [if file not found at desired location]
     */
    public function wolferhampton(){
        //Read json file and get content into an array format to parse through it
        try{
            $file = base_path(self::WOLFERHAMPTON_FILE);
            if(file_exists($file)){
                $jsonString = file_get_contents($file);
            }else{
                throw new FileNotFoundException($file);
            }
        } catch(FileNotFoundException $e){
            echo $e->getMessage();
            return;
        }


        $data = json_decode($jsonString, true);
        //create an array of horse names from Participants
        $horses = [];
        foreach($data['RawData']['Participants'] as $row){
            $horses[$row['Id']] = [
                'name'  =>  $row['Name']
            ];
        }

        //try and match the market selections based on horsenames and add price to horses array
        foreach($data['RawData']['Markets'][0]['Selections'] as $row){
            foreach ($horses as $key=>$horse){
                if($horse['name'] == $row['Tags']['name']){
                    $horses[$key]['price'] = $row['Price'];
                }
            }
        }

        //Sort Multi dimensial array based on horse price
        $horses = $this->sortByPrice($horses);

        return $horses;

    }

    public function sortByPrice($horses){
        usort($horses, function($a, $b) {
            return $a['price'] - $b['price'];
        });

        return $horses;
    }

}
