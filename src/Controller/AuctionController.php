<?php
namespace App\Controller;

class AuctionController extends AuctionBaseController
{
    // デフォルトテーブルを使わない
    public $useTable = false;

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadModel('Users');
        $this->loadModel('Biditems');
        $this->loadModel('Bidrequests');
        $this->loadModel('Bidinfo');
        $this->loadModel('Bidmessages');
        $this->set('authuser', $this->Auth->user());
        $this->viewBuilder()->setLayout('auction');
    }

    public function index()
    {
        $auction = $this->paginate('Biditems', ['order' => ['endtime' => 'desc'], 'limit' => 10]);
        $this->set(compact('auction'));
    }

    public function view($id = null)
    {
        $biditem = $this->Biditems->get($id, ['contain' => ['Users', 'Bidinfo', 'Bidinfo.Users']]);
        // オークション終了時の処理
        if ($biditem->endtime < new \Datetime('now') and $biditem->finished == 0) {
            $biditem->finished = 1;
            $this->Biditems->save($biditem);
            // Bidinfoを作成する
            $bidinfo = $this->Bidinfo->newEntity();
            $bidinfo->biditem_id = $id;
            // 最高金額のBidrequestを検索
            $bidrequest = $this->Bidrequests->find('all', [
                'conditions' => ['biditem_id' => $id],
                'contain' => ['Users'],
                'order' => ['price' => 'desc']
            ])->first();
            // Bidrequestが得られた時（入札が1件でもあった場合）の処理
            if (!empty($bidrequest)) {
                // Bidinfoの各種プロパティを設定して保存する
                $bidinfo->user_id = $bidrequest->user->id;
                $bidinfo->user = $bidrequest->user;
                $bidinfo->price = $bidrequest->price;
                $this->Bidinfo->save($bidinfo);
                // Biditemのbidinfoに$bidinfoを設定
                $biditem->bidinfo = $bidinfo;
            }
        }
        // Bidrequestsからbiditem_idが$idのものを取得
        $bidrequests = $this->Bidrequests->find('all', [
            'conditions' => ['biditem_id' => $id],
            'contain' => ['Users'],
            'order' => ['price' => 'desc']
        ])->toArray();
        $this->set(compact('biditem', 'bidrequests'));
    }

    public function add()
    {
        $biditem = $this->Biditems->newEntity();
        if ($this->request->is('post')) {
            // $biditem->user_id = $this->Auth->user('id');
            // $biditem->finished = 0;
            $data = $this->request->getData();
            $data['user_id'] = $this->Auth->user('id');
            $data['finished'] = 0;
            $biditem = $this->Biditems->patchEntity($biditem, $data);
            if ($this->Biditems->save($biditem)) {
                $this->Flash->success(__('保存しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('保存に失敗しました。もう一度入力して下さい。'));
        }
        $this->set(compact('biditem'));
    }

    public function bid($biditem_id = null)
    {
        $bidrequest = $this->Bidrequests->newEntity();
        if ($this->request->is('post')) {
            // $bidrequest->biditem_id = $biditem_id;
            // $bidrequest->user_id = $this->Auth->user('id');
            $data = $this->request->getData();
            $data['biditem_id'] = $biditem_id;
            $data['user_id'] = $this->Auth->user('id');
            $bidrequest = $this->Bidrequests->patchEntity($bidrequest, $data);
            if ($this->Bidrequests->save($bidrequest)) {
                $this->Flash->success(__('入札を送信しました。'));
                return $this->redirect(['action' => 'view', $biditem_id]);
            }
            $this->Flash->error(__('入札に失敗しました。もう一度入力して下さい。'));
        }
        $biditem = $this->Biditems->get($biditem_id);
        $this->set(compact('bidrequest', 'biditem'));
    }

    public function msg($bidinfo_id = null)
    {
        $bidmsg = $this->Bidmessages->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['bidinfo_id'] = $bidinfo_id;
            $data['user_id'] = $this->Auth->user('id');
            $bidmsg = $this->Bidmessages->patchEntity($bidmsg, $data);
            // $bidmsg->bidinfo_id = $bidinfo_id;
            // $bidmsg->user_id = $this->Auth->user('id');
            if ($this->Bidmessages->save($bidmsg)) {
                $this->Flash->success(__('保存しました。'));
            } else {
                $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
            }
        }
        try {
            $bidinfo = $this->Bidinfo->get($bidinfo_id, ['contain' => ['Biditems']]);
        } catch (\Exception $e) {
            $bidinfo = null;
        }
        $bidmsgs = $this->Bidmessages->find('all', [
            'conditions' => ['bidinfo_id' => $bidinfo_id],
            'contain' => ['Users'],
            'order' => ['created' => 'desc']
        ]);
        $this->set(compact('bidmsgs', 'bidinfo', 'bidmsg'));
    }

    // 自分の落札情報の表示
    public function home()
    {
        $bidinfo = $this->paginate('Bidinfo', [
            'conditions' => ['Bidinfo.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Biditems'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('bidinfo'));
    }

    // 自分の出品情報の表示
    public function home2()
    {
        $biditems = $this->paginate('Biditems', [
            'conditions' => ['Biditems.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Bidinfo'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('biditems'));
    }
}
