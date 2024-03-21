<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Admin\Bank;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BankController extends Controller
{
    /**
     * Method for view bank page
     * @return view
     */
    public function index(){
        $page_title     = "All Banks";
        $banks          = Bank::orderBy('id','desc')->get();
        
        return view('admin.sections.bank.index',compact(
            'page_title',
            'banks',
        ));
    }
    /**
     * Method for view create bank page
     * @return view
     */
    public function create(){
        $page_title     = "Bank Create";
        
        return view('admin.sections.bank.create',compact(
            'page_title',
        ));
    }
    /**
     * Method for store bank information
     * @param \Illuminate\Http\Request $request 
     */
    public function store(Request $request){
        $validator                  = Validator::make($request->all(),[
            'bank_name'             => 'required',
            'desc'                  => 'nullable',
            'label'                 => 'nullable|array',
            'label.*'               => 'nullable|string|max:50',
            'input_type'            => 'nullable|array',
            'input_type.*'          => 'nullable|string|max:20',
            'min_char'              => 'nullable|array',
            'min_char.*'            => 'nullable|numeric',
            'max_char'              => 'nullable|array',
            'max_char.*'            => 'nullable|numeric',
            'field_necessity'       => 'nullable|array',
            'field_necessity.*'     => 'nullable|string|max:20',
            'file_extensions'       => 'nullable|array',
            'file_extensions.*'     => 'nullable|string|max:255',
            'file_max_size'         => 'nullable|array',
            'file_max_size.*'       => 'nullable|numeric',
            'image'                 => 'required|mimes:png,jpg,webp,jpeg'
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        $validated                  = $validator->validate();
        if(Bank::where('bank_name',$validated['bank_name'])->exists()){
            throw ValidationException::withMessages([
                'name'  => "Bank Already Exists",
            ]);
        }
        
        $validated['slug']              = Str::slug($request->bank_name);
        $validated['desc']              = $validated['desc'];
        $validated['input_fields']      = decorate_input_fields($validated);
        
        $validated = Arr::except($validated,['label','input_type','min_char','max_char','field_necessity','file_extensions','file_max_size']);
        if($request->hasFile("image")){
            $validated['image'] = $this->imageValidate($request,"image",null);
        }
        
        try{
            Bank::create($validated);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('admin.bank.index')->with(['success' => ['Bank created successfully.']]);
    }
    /**
     * Method for view bank edit page
     * @return view
     */
    public function edit($slug){
        $page_title     = "Bank Edit";
        $bank           = Bank::where('slug',$slug)->first();
        if(!$bank) return back()->with(['error' => ['Sorry! Bank not found.']]);

        return view('admin.sections.bank.edit',compact(
            'page_title',
            'bank'
        ));
    }
    /**
     * Method for update bank information
     * @param $slug
     * @param \Illuminate\Http\Request $request
     */
    public function update(Request $request,$slug){
        $data       = Bank::where('slug',$slug)->first();
        if(!$data) return back()->with(['error' => ['Sorry! Bank not found.']]);
        $validator                  = Validator::make($request->all(),[
            'bank_name'              => 'required',
            'desc'                  => 'nullable',
            'label'                 => 'nullable|array',
            'label.*'               => 'nullable|string|max:50',
            'input_type'            => 'nullable|array',
            'input_type.*'          => 'nullable|string|max:20',
            'min_char'              => 'nullable|array',
            'min_char.*'            => 'nullable|numeric',
            'max_char'              => 'nullable|array',
            'max_char.*'            => 'nullable|numeric',
            'field_necessity'       => 'nullable|array',
            'field_necessity.*'     => 'nullable|string|max:20',
            'file_extensions'       => 'nullable|array',
            'file_extensions.*'     => 'nullable|string|max:255',
            'file_max_size'         => 'nullable|array',
            'file_max_size.*'       => 'nullable|numeric',
            'image'                 => 'nullable|mimes:png,jpg,webp,jpeg'
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        $validated                      = $validator->validate();
        if(Bank::whereNot('id',$data->id)->where('bank_name',$validated['bank_name'])->exists()){
            throw ValidationException::withMessages([
                'name'  => "Bank Already Exists",
            ]);
        }
        $validated['bank_name']         = $validated['bank_name'];
        $validated['desc']              = $validated['desc'];
        $validated['input_fields']      = decorate_input_fields($validated);
        
        $validated = Arr::except($validated,['label','input_type','min_char','max_char','field_necessity','file_extensions','file_max_size']);
        if($request->hasFile('image')){
            $validated['image']  =  $this->imageValidate($request,"image",null);
        }

        try{
            $data->update($validated);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('admin.bank.index')->with(['success' => ['Bank Updated successfully.']]);
    }
    /**
     * Method for delete bank
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function delete(Request $request){
        $request->validate([
            'target'    => 'required|numeric|',
        ]);
        $bank = Bank::find($request->target);
    
        try {
            $bank->delete();
        } catch (Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank Deleted Successfully!']]);
    }
    /**
     * Method for status update for Outside wallet
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric|exists:banks,id',
            'status'            => 'required|boolean',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();


        $bank = Bank::find($validated['data_target']);

        try{
            $bank->update([
                'status'        => ($validated['status']) ? false : true,
            ]);
        }catch(Exception $e) {
            $errors = ['error' => ['Something went wrong! Please try again.'] ];
            return Response::error($errors,null,500);
        }

        $success = ['success' => ['Bank status updated successfully!']];
        return Response::success($success);
    }
    /**
     * Method for image validate
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
    */
    public function imageValidate($request,$input_name,$old_image = null) {
        if($request->hasFile($input_name)) {
            $image_validated = Validator::make($request->only($input_name),[
                $input_name         => "image|mimes:png,jpg,webp,jpeg,svg",
            ])->validate();

            $image = get_files_from_fileholder($request,$input_name);
            $upload = upload_files_from_path_dynamic($image,'bank',$old_image);
            return $upload;
        }
        return false;
    }
}
