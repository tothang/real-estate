<?php

namespace App\Http\Controllers\Admin;

use App\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Toastr;

class ServiceController extends Controller
{

    public function index()
    {
        $services = Service::latest()->get();

        return view('admin.services.index', compact('services'));
    }


    public function create()
    {
        return view('admin.services.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required',
            'description'   => 'required|max:200',
            'icon'          => 'required',
            'service_order' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png'
        ]);
        $image = $request->file('image');
        if(isset($image)){
            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();

            if(!Storage::disk('public')->exists('services')){
                Storage::disk('public')->makeDirectory('services');
            }
            $slider = Image::make($image)->resize(1600, 480)->save();
            Storage::disk('public')->put('services/'.$imagename, $slider);
        }else{
            $imagename = 'default.png';
        }
        $service = new Service();
        $service->title         = $request->title;
        $service->description   = $request->description;
        $service->icon          = $request->icon;
        $service->service_order = $request->service_order;
        $service->image          = $imagename;
        $service->save();

        Toastr::success('message', 'Service created successfully.');
        return redirect()->route('admin.services.index');
    }


    public function edit(Service $service)
    {
        $service = Service::findOrFail($service->id);

        return view('admin.services.edit', compact('service'));
    }


    public function update(Request $request, Service $service)
    {
        $request->validate([
            'title'         => 'required',
            'description'   => 'required|max:200',
            'icon'          => 'required',
            'service_order' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png'
        ]);
        $image = $request->file('image');
        if(isset($image)){
            $currentDate = Carbon::now()->toDateString();
            $imagename = $currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            if(!Storage::disk('public')->exists('services')){
                Storage::disk('public')->makeDirectory('services');
            }
            if(Storage::disk('public')->exists('services/'.$service->image)){
                Storage::disk('public')->delete('services/'.$service->image);
            }
            $sliderimg = Image::make($image)->resize(1600, 480)->save();
            Storage::disk('public')->put('services/'.$imagename, $sliderimg);
        }else{
            $imagename = $service->image;
        }

        $service = Service::findOrFail($service->id);
        $service->title         = $request->title;
        $service->description   = $request->description;
        $service->icon          = $request->icon;
        $service->service_order = $request->service_order;
        $service->image         = $imagename;
        $service->save();

        Toastr::success('message', 'Service updated successfully.');
        return redirect()->route('admin.services.index');
    }


    public function destroy(Service $service)
    {
        $service = Service::findOrFail($service->id);
        $service->delete();

        Toastr::success('message', 'Service deleted successfully.');
        return back();
    }
}
