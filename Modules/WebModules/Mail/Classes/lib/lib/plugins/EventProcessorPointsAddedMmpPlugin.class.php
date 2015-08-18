<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EventProcessorPointsAddedPlugin
 *
 * @author p.gorbachev
 */
class EventProcessorPointsAddedMmpPlugin extends EventProcessorBasePlugin
{

    public function process(\ICRMRabbitManager $manager)
    {
        print_r($manager->getEnvelope());
    }

}