<?php

namespace App\Enums;

enum SortProductBy:string {
    case MOST_STOCK = 'most stock';
    case LEAST_STOCK = 'least stock';
    case BEST_SELLING = 'best selling';
    case ALPHABETICALLY = 'alphabetically';
    case MOST_EXPENSIVE = 'most expensive';
    case MOST_AFFORDABLE = 'most affordable';
    case MOST_DISCOUNTED = 'most discounted';
}
