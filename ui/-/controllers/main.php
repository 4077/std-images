<?php namespace std\images\ui\controllers;

class Main extends \Controller
{
    private $imageable;

    private $imageableInstance;

    public function __create()
    {
        $this->d('|', [
            'href' => false
        ]);

        $this->dmap('|', 'target, imageable, instance, cache_field, href, callbacks, dev_info');

        $this->imageable = $this->unpackModel('imageable');

        if ($this->data('imageable')) {
            $this->imageable = $this->unpackModel('imageable');
        }

        if ($this->data('target')) {
            $this->imageable = \std\images\Target::get($this->data['target']);
        }

        $this->imageableInstance = $this->data('instance');

        $sInstance = path(underscore_model($this->imageable), $this->imageableInstance);

        $this->s('|' . $sInstance, [
            'selection' => []
        ]);

        $this->s(false, [
            'click_mode' => 'open' // open/select
        ]);
    }

    public function performCallback($name)
    {
        $callbacks = $this->d('~:callbacks|');

        if (isset($callbacks[$name])) {
            $call = \ewma\Data\Data::tokenize($callbacks[$name], [
                '%imageable' => $this->imageable
            ]);

            $this->_call($call)->perform();
        }
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        pusher()->subscribe();

        $v->assign([
                       'TOP_BAR'    => $this->c('>topBar:view|'),
                       'IMAGES'     => $this->c('>images/grid:view|'),
                       'BOTTOM_BAR' => $this->c('>bottomBar:view|')
                   ]);

        $this->css(':\css\std~');

        $this->c('\plugins\cssElementQueries~:load');

        $this->c('\std\ui\dialogs~:addContainer:std/images');

        return $v;
    }
}
