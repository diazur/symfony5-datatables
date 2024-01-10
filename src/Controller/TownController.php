<?php

namespace App\Controller;

use App\Repository\TownRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TownController extends AbstractController
{
    /**
     * @Route("/town", name="app_town_list")
     */
    public function list(): Response
    {
        return $this->render('town/list.html.twig');
    }

    /**
     * @Route("/town/datatable-list", name="app_datatable_list", methods={"POST"})
     */
    public function datatableList(Request $request, TownRepository $repository): Response
    {
        $draw = $request->request->getInt('draw');
        $start = $request->request->getInt('start');
        $length = $request->request->getInt('length');
        $orders = $request->request->get('order');
        $search = $request->request->get('search');
        $columns = $request->request->get('columns');

        $result = $repository->getDatatableData($start, $length, $orders, $search, $columns);
        $records = $result['records'];
        $recordsFiltered = $result['totalFiltered'];

        $data = [];

        foreach ($records as $record) {
            $row = [];
            $row[] = $record->getName();
            $row[] = $record->getPostalCode();
            $row[] = $record->getDepartment()->getName();
            $row[] = $record->getDepartment()->getRegion()->getName();
            $data[] = $row;
        }

        $recordsTotal = $repository->count([]);

        $result = [
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ];
        $json = json_encode($result);

        return new Response($json);
    }
}
