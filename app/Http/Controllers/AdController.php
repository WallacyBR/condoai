<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\AdInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ad::with('images', 'user');

        // Filtering
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->has('condition')) {
            $query->where('condition', $request->input('condition'));
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }
        if ($request->has('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $ads = $query->paginate(15);

        return response()->json($ads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'category' => 'required|string|max:255',
            'condition' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after_or_equal:today',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $ad = Ad::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'currency' => $request->currency ?? 'BRL',
                'category' => $request->category,
                'condition' => $request->condition,
                'location' => $request->location,
                'contact_info' => $request->contact_info,
                'expires_at' => $request->expires_at,
                'status' => 'Ativo',
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $order => $image) {
                    $path = $image->store('ad_images', 'public');
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image_path' => $path,
                        'order' => $order,
                    ]);
                }
            }

            DB::commit();
            return response()->json($ad->load('images'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating ad', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ad $ad, Request $request)
    {
        // Increment views count
        $ad->increment('views_count');

        // Record interaction
        AdInteraction::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::check() ? Auth::id() : null,
            'interaction_type' => 'View',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($ad->load('images', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'category' => 'required|string|max:255',
            'condition' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after_or_equal:today',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'existing_image_ids' => 'nullable|array',
            'existing_image_ids.*' => 'uuid|exists:ad_images,id',
        ]);

        DB::beginTransaction();
        try {
            $ad->update($request->only([
                'title', 'description', 'price', 'currency', 'category',
                'condition', 'location', 'contact_info', 'expires_at',
            ]));

            // Handle image updates
            $existingImageIds = $request->input('existing_image_ids', []);
            $currentImageIds = $ad->images->pluck('id')->toArray();

            // Delete removed images
            $imagesToDelete = array_diff($currentImageIds, $existingImageIds);
            foreach ($imagesToDelete as $imageId) {
                $image = AdImage::find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            // Add new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $order => $image) {
                    $path = $image->store('ad_images', 'public');
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image_path' => $path,
                        'order' => $order + count($existingImageIds), // Adjust order for new images
                    ]);
                }
            }

            DB::commit();
            return response()->json($ad->load('images'), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating ad', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            // Delete associated images from storage
            foreach ($ad->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            $ad->images()->delete(); // Delete image records
            $ad->interactions()->delete(); // Delete interaction records
            $ad->delete(); // Soft delete the ad

            DB::commit();
            return response()->json(['message' => 'Ad deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting ad', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark an ad as sold.
     */
    public function markAsSold(Ad $ad, Request $request)
    {
        if ($ad->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ad->update(['status' => 'Vendido']);

        AdInteraction::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::id(),
            'interaction_type' => 'Mark_Sold',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Ad marked as sold', 'ad' => $ad], 200);
    }

    /**
     * Mark an ad as unsold.
     */
    public function markAsUnsold(Ad $ad, Request $request)
    {
        if ($ad->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ad->update(['status' => 'Ativo']);

        AdInteraction::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::id(),
            'interaction_type' => 'Mark_Unsold',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Ad marked as unsold', 'ad' => $ad], 200);
    }

    /**
     * Report an ad.
     */
    public function reportAd(Ad $ad, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $ad->increment('reported_count');

        AdInteraction::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::check() ? Auth::id() : null,
            'interaction_type' => 'Report',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['reason' => $request->reason],
        ]);

        return response()->json(['message' => 'Ad reported successfully'], 200);
    }

    /**
     * Record contact click interaction.
     */
    public function contactClick(Ad $ad, Request $request)
    {
        AdInteraction::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::check() ? Auth::id() : null,
            'interaction_type' => 'Contact_Click',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Contact click recorded'], 200);
    }

    /**
     * Get ads created by the authenticated user.
     */
    public function myAds()
    {
        $ads = Auth::user()->ads()->with('images')->paginate(15);
        return response()->json($ads);
    }

    /**
     * Get statistics for a specific ad.
     */
    public function adStatistics(Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $statistics = [
            'total_views' => $ad->views_count,
            'total_reports' => $ad->reported_count,
            'contact_clicks' => $ad->interactions()->where('interaction_type', 'Contact_Click')->count(),
            'marked_sold' => $ad->interactions()->where('interaction_type', 'Mark_Sold')->count(),
            'marked_unsold' => $ad->interactions()->where('interaction_type', 'Mark_Unsold')->count(),
            'status' => $ad->status,
        ];

        return response()->json($statistics);
    }
}