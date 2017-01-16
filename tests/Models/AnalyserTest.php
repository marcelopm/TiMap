<?php

namespace Tests\Models;

use App\Models\Analyser;
use App\Models\Tables\Analysers;
use App\Helpers\Image as ImageHelper;

class AnalyserTest extends Base {

    public function testGetHeaviest() {
        $lite = factory(Analyser::class)->create();
        $heavy = factory(Analyser::class)->states('heavy')->create();

        $heaviest = Analysers::getHeaviest();

        $this->assertEquals(true, (int) $lite->weight < (int) $heaviest->weight);
    }

    public function testGetFromAnalysisHash() {
        $analyser = factory(Analyser::class)->create();

        $hash = ImageHelper::createAnalysisHash($analyser->name, $analyser->id);
        
        $retrievedAnalyser = Analysers::getFromAnalysisHash($analyser->id, $hash);

        $this->assertEquals(true, (int)$retrievedAnalyser->id === (int)$analyser->id);
    }

}
