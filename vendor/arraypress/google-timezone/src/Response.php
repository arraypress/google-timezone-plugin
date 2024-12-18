<?php
/**
 * Google Maps Timezone API Response Class
 *
 * This class handles and structures the response data from the Google Maps Timezone API.
 * It provides methods to access timezone information and related data for a specific location.
 *
 * @package     ArrayPress/Utils
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Google\Timezone;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Response
 *
 * @package ArrayPress\Google\Timezone
 */
class Response {

	/**
	 * Raw response data from the API
	 *
	 * @var array{
	 *     dstOffset: int,
	 *     rawOffset: int,
	 *     timeZoneId: string,
	 *     timeZoneName: string,
	 *     status: string
	 * }
	 */
	private array $data;

	/**
	 * Initialize the response object
	 *
	 * @param array $data Raw response data from Timezone API
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * Get the timezone ID
	 *
	 * @return string|null The IANA timezone ID (e.g., "America/New_York")
	 */
	public function get_timezone_id(): ?string {
		return $this->data['timeZoneId'] ?? null;
	}

	/**
	 * Get the localized timezone name
	 *
	 * @return string|null The human-readable timezone name
	 */
	public function get_timezone_name(): ?string {
		return $this->data['timeZoneName'] ?? null;
	}

	/**
	 * Get the raw UTC offset in seconds
	 *
	 * @return int|null The offset from UTC in seconds
	 */
	public function get_raw_offset(): ?int {
		return $this->data['rawOffset'] ?? null;
	}

	/**
	 * Get the DST offset in seconds
	 *
	 * @return int|null The daylight savings time offset in seconds
	 */
	public function get_dst_offset(): ?int {
		return $this->data['dstOffset'] ?? null;
	}

	/**
	 * Get the total offset (raw + DST) in seconds
	 *
	 * @return int|null The total offset from UTC in seconds
	 */
	public function get_total_offset(): ?int {
		$raw_offset = $this->get_raw_offset();
		$dst_offset = $this->get_dst_offset();

		if ( $raw_offset === null || $dst_offset === null ) {
			return null;
		}

		return $raw_offset + $dst_offset;
	}

	/**
	 * Check if the location is currently observing DST
	 *
	 * @return bool True if DST is active
	 */
	public function is_dst(): bool {
		return ( $this->get_dst_offset() ?? 0 ) > 0;
	}

	/**
	 * Get the formatted UTC offset string
	 *
	 * @param bool $include_dst Whether to include DST offset in the calculation
	 *
	 * @return string|null Formatted offset string (e.g., "+05:30" or "-08:00")
	 */
	public function get_formatted_offset( bool $include_dst = true ): ?string {
		$offset = $include_dst ? $this->get_total_offset() : $this->get_raw_offset();

		if ( $offset === null ) {
			return null;
		}

		$absolute_offset = abs( $offset );
		$hours           = floor( $absolute_offset / 3600 );
		$minutes         = floor( ( $absolute_offset % 3600 ) / 60 );

		return sprintf(
			'%s%02d:%02d',
			$offset >= 0 ? '+' : '-',
			$hours,
			$minutes
		);
	}

	/**
	 * Get DateTime object with the timezone set
	 *
	 * @param int|null $timestamp Optional timestamp (defaults to current time)
	 *
	 * @return DateTime|null DateTime object with the timezone set
	 */
	public function get_datetime( ?int $timestamp = null ): ?DateTime {
		$timezone_id = $this->get_timezone_id();
		if ( ! $timezone_id ) {
			return null;
		}

		try {
			$timezone = new DateTimeZone( $timezone_id );
			$datetime = new DateTime( 'now', $timezone );

			if ( $timestamp !== null ) {
				$datetime->setTimestamp( $timestamp );
			}

			return $datetime;
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Check if the timezone is valid
	 *
	 * @return bool True if timezone data is valid
	 */
	public function is_valid(): bool {
		return isset( $this->data['timeZoneId'] ) &&
		       isset( $this->data['rawOffset'] ) &&
		       isset( $this->data['dstOffset'] );
	}

	/**
	 * Get the abbreviated timezone name
	 *
	 * @return string|null Abbreviated timezone name (e.g., "EST", "PDT")
	 */
	public function get_abbreviated_name(): ?string {
		$datetime = $this->get_datetime();
		if ( ! $datetime ) {
			return null;
		}

		return $datetime->format( 'T' );
	}

	/**
	 * Get the timezone information as an array
	 *
	 * @param bool $include_datetime Whether to include current datetime information
	 *
	 * @return array{
	 *     timezone_id: string|null,
	 *     timezone_name: string|null,
	 *     abbreviated_name: string|null,
	 *     raw_offset: int|null,
	 *     dst_offset: int|null,
	 *     total_offset: int|null,
	 *     formatted_offset: string|null,
	 *     is_dst: bool,
	 *     current_time?: string,
	 *     current_date?: string
	 * } Timezone information array
	 */
	public function to_array( bool $include_datetime = false ): array {
		$data = [
			'timezone_id'      => $this->get_timezone_id(),
			'timezone_name'    => $this->get_timezone_name(),
			'abbreviated_name' => $this->get_abbreviated_name(),
			'raw_offset'       => $this->get_raw_offset(),
			'dst_offset'       => $this->get_dst_offset(),
			'total_offset'     => $this->get_total_offset(),
			'formatted_offset' => $this->get_formatted_offset(),
			'is_dst'           => $this->is_dst()
		];

		if ( $include_datetime ) {
			$datetime = $this->get_datetime();
			if ( $datetime ) {
				$data['current_time'] = $datetime->format( 'H:i:s' );
				$data['current_date'] = $datetime->format( 'Y-m-d' );
			}
		}

		return $data;
	}

}